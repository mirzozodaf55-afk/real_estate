<?php

namespace Tests\Feature;

use App\Events\NotifyMe;
use App\Mail\ModerationResultMail;
use App\Models\Announ;
use App\Models\AnnounNotification;
use App\Models\ModerationLog;
use App\Models\Owner;
use App\Models\Pictures;
use App\Models\RejectionReason;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\RefreshTestDatabase;
use Tests\TestCase;

/** Решения модератора: статус, журнал, уведомление владельцу, повторные клики, очередь */
class ModerationTest extends TestCase
{
    use RefreshTestDatabase;

    private Owner $moderator;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([NotifyMe::class]);
        Mail::fake();
        $this->moderator = Owner::factory()->moderator()->create();
        Sanctum::actingAs($this->moderator);
    }

    public function test_approve_publishes_logs_and_notifies_owner(): void
    {
        $announ = Announ::factory()->create();

        $this->postJson("/api/admin/announs/{$announ->id}/approve")->assertOk()
            ->assertJson(['message' => 'Объявление одобрено']);

        $announ->refresh();
        $this->assertSame(Announ::APPROVED, $announ->moderation_status);
        $this->assertSame($this->moderator->id, $announ->moderated_by);
        $this->assertDatabaseHas('moderation_logs', ['announ_id' => $announ->id, 'action' => 'approve', 'moderator_id' => $this->moderator->id]);
        $this->assertDatabaseHas('announ_notifications', ['user_id' => $announ->owner_id, 'announ_id' => $announ->id, 'kind' => AnnounNotification::KIND_APPROVED]);
        Event::assertDispatched(NotifyMe::class, fn ($e) => $e->broadcastOn()[0]->name === "private-user.{$announ->owner_id}");
        Mail::assertSent(ModerationResultMail::class, fn ($m) => $m->hasTo($announ->owner->email));
        $this->assertNotNull(Announ::find($announ->id)); // теперь видно в поиске
    }

    /** Двойной клик или два модератора сразу: второе одобрение ничего не меняет и не пишет владельцу */
    public function test_repeated_approve_is_idempotent(): void
    {
        $announ = Announ::factory()->create();

        $this->postJson("/api/admin/announs/{$announ->id}/approve")->assertOk();
        $this->postJson("/api/admin/announs/{$announ->id}/approve")->assertOk()
            ->assertJson(['message' => 'Объявление уже одобрено']);

        $this->assertSame(1, ModerationLog::where('announ_id', $announ->id)->count());
        Mail::assertSentCount(1);
    }

    public function test_reject_requires_reason(): void
    {
        $announ = Announ::factory()->create();

        $this->postJson("/api/admin/announs/{$announ->id}/reject")->assertStatus(422)->assertJsonValidationErrors('reason');
        $this->postJson("/api/admin/announs/{$announ->id}/reject", ['reason_id' => 999999])->assertStatus(422)
            ->assertJsonValidationErrors(['reason_id' => 'Такой причины отказа нет — обновите страницу']);

        $this->assertSame(Announ::PENDING, $announ->fresh()->moderation_status);
    }

    /** Владелец получает готовую причину по-русски, даже если модератор видит её по-английски */
    public function test_reject_with_preset_reason_sends_russian_text_to_owner(): void
    {
        $announ = Announ::factory()->create();
        $reason = RejectionReason::create(['title' => 'Фото', 'text' => 'Фото не подходят к объявлению.', 'title_en' => 'Photos', 'text_en' => 'Photos do not match.', 'sort' => 1, 'active' => true]);

        $this->postJson("/api/admin/announs/{$announ->id}/reject", ['reason_id' => $reason->id, 'reason' => 'Нужен вид из окна.', 'lang' => 'en'])
            ->assertOk();

        $expected = 'Фото не подходят к объявлению. Нужен вид из окна.';
        $this->assertSame(Announ::REJECTED, $announ->fresh()->moderation_status);
        $this->assertSame($expected, $announ->fresh()->moderation_reason);
        $this->assertDatabaseHas('announ_notifications', ['announ_id' => $announ->id, 'kind' => AnnounNotification::KIND_REJECTED, 'message' => $expected]);
        $this->assertNull(Announ::find($announ->id)); // в поиске и для посетителей его нет
    }

    public function test_needs_changes_requires_comment(): void
    {
        $announ = Announ::factory()->create();

        $this->postJson("/api/admin/announs/{$announ->id}/needs-changes", ['comment' => ''])->assertStatus(422);
        $this->postJson("/api/admin/announs/{$announ->id}/needs-changes", ['comment' => 'Добавьте фото кухни'])->assertOk();

        $this->assertSame(Announ::NEEDS_CHANGES, $announ->fresh()->moderation_status);
        $this->assertSame('Добавьте фото кухни', $announ->fresh()->moderation_reason);
    }

    /** Одно объявление уже одобрено: в счётчике и в письмах — только те, что поменялись */
    public function test_bulk_counts_only_changed_listings(): void
    {
        $already = Announ::factory()->approved()->create();
        $pending = Announ::factory()->count(2)->create();

        $this->postJson('/api/admin/announs/bulk', ['ids' => [$already->id, ...$pending->pluck('id')], 'action' => 'approve'])
            ->assertOk()->assertJson(['count' => 2, 'message' => 'Одобрено: 2']);

        $this->assertSame(0, ModerationLog::where('announ_id', $already->id)->count());
        Mail::assertSentCount(2);
    }

    /** Следующее в очереди — по риску от ИИ, как в списке модератора, а не по номеру */
    public function test_next_id_follows_queue_order(): void
    {
        $current = Announ::factory()->create();
        Announ::factory()->reviewedByAi('low')->create();
        $highRisk = Announ::factory()->reviewedByAi('high')->create();

        $this->postJson("/api/admin/announs/{$current->id}/approve")->assertOk()->assertJson(['next_id' => $highRisk->id]);
        $this->getJson('/api/admin/announs')->assertOk()->assertJsonPath('data.0.id', $highRisk->id);
    }

    /** Иначе одобренный безобидный текст можно было бы потом заменить чем угодно */
    public function test_owner_edit_sends_approved_listing_back_to_moderation(): void
    {
        $announ = Announ::factory()->approved()->create();
        $picture = Pictures::create(['announ_id' => $announ->id, 'pictures' => 'test.webp', 'position' => 0, 'rotation' => 0]);
        Sanctum::actingAs($announ->owner);

        $this->postJson("/api/my/announs/{$announ->id}", [
            'announ' => json_encode(['title' => 'Новый заголовок', 'price' => 150000]),
            'photos' => json_encode([['id' => $picture->id, 'rotation' => 0]]),
        ])->assertOk();

        $this->assertSame(Announ::PENDING, $announ->fresh()->moderation_status);
        $this->getJson("/api/announs/{$announ->id}")->assertOk()->assertJsonPath('data.is_public', false);
    }
}
