<?php

namespace Tests\Feature;

use App\Events\NotifyMe;
use App\Models\Announ;
use App\Models\ModerationLog;
use App\Models\SiteSetting;
use App\Services\AutoModerator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshTestDatabase;
use Tests\TestCase;

/** Решения без модератора по итогам ИИ-проверки (App\Services\AutoModerator) */
class AutoModerationTest extends TestCase
{
    use RefreshTestDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([NotifyMe::class]);
        Mail::fake();
    }

    public function test_off_leaves_everything_to_moderator(): void
    {
        $announ = Announ::factory()->reviewedByAi('low')->create();

        $this->assertNull(AutoModerator::run($announ));
        $this->assertSame(Announ::PENDING, $announ->fresh()->moderation_status);
    }

    public function test_without_fresh_ai_review_listing_waits_for_moderator(): void
    {
        SiteSetting::put('auto_moderation', AutoModerator::FULL);
        $announ = Announ::factory()->create();

        $this->assertNull(AutoModerator::run($announ));
    }

    public function test_approve_mode_publishes_clean_low_risk_listing(): void
    {
        SiteSetting::put('auto_moderation', AutoModerator::APPROVE);
        $announ = Announ::factory()->reviewedByAi('low')->create();

        $this->assertSame(Announ::APPROVED, AutoModerator::run($announ));
        $this->assertDatabaseHas('moderation_logs', ['announ_id' => $announ->id, 'action' => 'approve', 'moderator_id' => null]);
    }

    public function test_approve_mode_keeps_medium_risk_for_moderator(): void
    {
        SiteSetting::put('auto_moderation', AutoModerator::APPROVE);
        $announ = Announ::factory()->reviewedByAi('medium')->create();

        $this->assertNull(AutoModerator::run($announ));
    }

    public function test_full_mode_rejects_high_risk_with_reason_from_ai(): void
    {
        SiteSetting::put('auto_moderation', AutoModerator::FULL);
        $announ = Announ::factory()->reviewedByAi('high')->create();

        $this->assertSame(Announ::REJECTED, AutoModerator::run($announ));
        $this->assertSame('Цена не похожа на настоящую', $announ->fresh()->moderation_reason);
    }

    /** Копию или повторную подачу решает человек, даже в полном режиме */
    public function test_full_mode_leaves_duplicates_to_moderator(): void
    {
        SiteSetting::put('auto_moderation', AutoModerator::FULL);
        Announ::factory()->approved()->create(['title' => 'Просторная квартира в центре Душанбе у парка']);
        $copy = Announ::factory()->reviewedByAi('low')->create(['title' => 'Просторная квартира в центре Душанбе у парка']);

        $this->assertNull(AutoModerator::run($copy));
        $this->assertSame(Announ::PENDING, $copy->fresh()->moderation_status);
        $this->assertSame(0, ModerationLog::where('announ_id', $copy->id)->count());
    }

    /** Владелец поправил объявление после проверки ИИ — старый вывод к новому тексту не относится */
    public function test_review_older_than_edit_is_ignored(): void
    {
        SiteSetting::put('auto_moderation', AutoModerator::FULL);
        $announ = Announ::factory()->reviewedByAi('low')->create();
        $announ->forceFill(['ai_review_at' => now()->subMinute()])->save();

        $this->assertNull(AutoModerator::run($announ));
    }
}
