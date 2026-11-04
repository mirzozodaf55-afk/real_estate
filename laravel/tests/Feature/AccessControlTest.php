<?php

namespace Tests\Feature;

use App\Models\Announ;
use App\Models\Owner;
use Laravel\Sanctum\Sanctum;
use Tests\RefreshTestDatabase;
use Tests\TestCase;

/** Кто куда может зайти: гость, пользователь, модератор, админ, заблокированный */
class AccessControlTest extends TestCase
{
    use RefreshTestDatabase;

    public function test_guest_gets_401_on_private_routes(): void
    {
        $routes = [
            ['GET', '/api/get_user'],
            ['GET', '/api/my/announs'],
            ['GET', '/api/get_all_users'],
            ['POST', '/api/send-message'],
            ['POST', '/api/send_message'],
            ['POST', '/api/avatar'],
            ['POST', '/api/create/announ'],
            ['GET', '/api/admin/stats'],
            ['GET', '/api/admin/users'],
        ];
        foreach ($routes as [$method, $uri]) {
            $this->json($method, $uri)->assertStatus(401);
        }
    }

    public function test_user_cannot_open_admin(): void
    {
        Sanctum::actingAs(Owner::factory()->create());

        $this->getJson('/api/admin/stats')->assertForbidden();
        $this->getJson('/api/admin/announs')->assertForbidden();
        $this->postJson('/api/admin/announs/1/approve')->assertForbidden();
        $this->getJson('/api/admin/users')->assertForbidden();
    }

    public function test_moderator_gets_moderation_but_not_admin_only_sections(): void
    {
        Sanctum::actingAs(Owner::factory()->moderator()->create());

        $this->getJson('/api/admin/stats')->assertOk();
        $this->getJson('/api/admin/announs')->assertOk();

        $this->getJson('/api/admin/users')->assertForbidden();
        $this->getJson('/api/admin/chats')->assertForbidden();
        $this->postJson('/api/admin/settings/auto-moderation', ['mode' => 'full'])->assertForbidden();
        $this->postJson('/api/admin/users/1/role', ['role' => 'admin'])->assertForbidden();
    }

    public function test_admin_gets_admin_only_sections(): void
    {
        Sanctum::actingAs(Owner::factory()->admin()->create());

        $this->getJson('/api/admin/users')->assertOk();
        $this->getJson('/api/admin/chats')->assertOk();
    }

    public function test_blocked_user_cannot_act(): void
    {
        Sanctum::actingAs(Owner::factory()->blocked()->create());

        $this->getJson('/api/get_user')->assertForbidden()->assertJson(['blocked' => true]);
        $this->getJson('/api/my/announs')->assertForbidden();
        $this->postJson('/api/send_message', ['email' => 'new@example.com'])->assertForbidden();
    }

    public function test_blocked_moderator_cannot_moderate(): void
    {
        Sanctum::actingAs(Owner::factory()->moderator()->blocked()->create());

        $this->getJson('/api/admin/announs')->assertForbidden();
    }

    /** Эти маршруты отдавали чужие данные или меняли чужие объявления — сайт их не использует */
    public function test_removed_unsafe_routes_stay_removed(): void
    {
        Sanctum::actingAs(Owner::factory()->create());

        foreach (['/api/get_user_with_announ', '/api/get_video', '/api/save-pictures', '/api/upload-image',
            '/api/get_announs', '/api/get_data', '/api/video-sync', '/api/create/city', '/api/create/infrastructure',
            '/api/create/furniture'] as $uri) {
            $this->getJson($uri)->assertNotFound();
            $this->postJson($uri)->assertNotFound();
        }
    }

    public function test_user_cannot_touch_someone_elses_listing(): void
    {
        $announ = Announ::factory()->approved()->create();
        Sanctum::actingAs(Owner::factory()->create());

        $this->getJson("/api/my/announs/{$announ->id}")->assertNotFound();
        $this->postJson("/api/my/announs/{$announ->id}", ['announ' => '{"price":1}', 'photos' => '[]'])->assertNotFound();
        $this->postJson("/api/my/announs/{$announ->id}/unpublish")->assertNotFound();
        $this->postJson("/api/my/announs/{$announ->id}/sold")->assertNotFound();
        $this->deleteJson("/api/my/announs/{$announ->id}")->assertNotFound();

        $this->assertNotSoftDeleted($announ);
        $this->assertNull($announ->fresh()->sold_at);
    }

    public function test_my_listings_show_only_own(): void
    {
        $owner = Owner::factory()->create();
        $own = Announ::factory()->for($owner, 'owner')->create();
        Announ::factory()->approved()->create();
        Sanctum::actingAs($owner);

        $this->getJson('/api/my/announs')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id);
    }

    public function test_unmoderated_listing_is_visible_only_to_owner_and_staff(): void
    {
        $announ = Announ::factory()->create();

        $this->getJson("/api/announs/{$announ->id}")->assertNotFound();

        Sanctum::actingAs(Owner::factory()->create());
        $this->getJson("/api/announs/{$announ->id}")->assertNotFound();

        Sanctum::actingAs($announ->owner);
        $this->getJson("/api/announs/{$announ->id}")->assertOk()->assertJsonPath('data.is_public', false);

        Sanctum::actingAs(Owner::factory()->moderator()->create());
        $this->getJson("/api/announs/{$announ->id}")->assertOk();
    }

    public function test_guest_does_not_see_owner_contacts(): void
    {
        $announ = Announ::factory()->approved()->create();

        $this->getJson("/api/announs/{$announ->id}")->assertOk()
            ->assertJsonMissingPath('data.owner.phone_number')
            ->assertJsonMissingPath('data.owner.email');
    }

    /** Выводы ИИ («подозрительно низкая цена…») — внутренние заметки модерации */
    public function test_ai_review_is_hidden_from_public_and_shown_to_moderators(): void
    {
        $announ = Announ::factory()->approved()->reviewedByAi()->create();

        foreach (['ai_review', 'ai_risk', 'photo_check', 'moderated_by'] as $field) {
            $this->getJson("/api/announs/{$announ->id}")->assertJsonMissingPath("data.$field");
            $this->postJson('/api/announs/search')->assertJsonPath('data.0.id', $announ->id)->assertJsonMissingPath("data.0.$field");
        }

        Sanctum::actingAs(Owner::factory()->moderator()->create());
        $this->getJson("/api/admin/announs/{$announ->id}")->assertOk()->assertJsonPath('data.ai_risk', 'high');
        $this->getJson('/api/admin/announs?status=approved')->assertOk()->assertJsonPath('data.0.ai_risk', 'high');
    }

    /** Имя файла из адреса не должно выводить за пределы папки (раньше так скачивался .env) */
    public function test_file_routes_do_not_leave_their_folder(): void
    {
        $this->get('/api/image/..%2F..%2F.env')->assertNotFound();
        $this->get('/api/image/small/..%2F..%2F.env')->assertNotFound();
        $this->get('/api/image/./index.php')->assertNotFound();
        $this->get('/api/filemessage/../index.php')->assertNotFound();
        $this->get('/api/filemessage/..%2F..%2F.env')->assertNotFound();
        $this->get('/api/filemessage/x/index.php')->assertNotFound();
    }
}
