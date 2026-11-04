<?php

namespace Tests\Feature;

use App\Models\Owner;
use App\Support\Demo;
use Laravel\Sanctum\Sanctum;
use Tests\RefreshTestDatabase;
use Tests\TestCase;

/** Публичное демо (DEMO_MODE): вход одной кнопкой и закрытые для посетителей действия */
class DemoModeTest extends TestCase
{
    use RefreshTestDatabase;

    private function enableDemo(): void
    {
        config(['app.demo' => true]);
    }

    public function test_demo_login_is_unavailable_outside_demo(): void
    {
        Owner::factory()->create(['email' => Demo::ACCOUNTS['admin']]);

        $this->postJson('/api/demo/login', ['role' => 'admin'])->assertNotFound();
    }

    public function test_demo_login_gives_token_of_demo_account(): void
    {
        $this->enableDemo();
        $moderator = Owner::factory()->moderator()->create(['email' => Demo::ACCOUNTS['moderator']]);

        $token = $this->postJson('/api/demo/login', ['role' => 'moderator'])->assertOk()
            ->assertJsonPath('user.id', $moderator->id)->json('token');

        $this->withToken($token)->getJson('/api/admin/stats')->assertOk();
    }

    /** Войти можно только под демо-аккаунтами, а не под любым пользователем */
    public function test_demo_login_accepts_only_known_roles(): void
    {
        $this->enableDemo();

        $this->postJson('/api/demo/login', ['role' => 'root'])->assertStatus(422);
        $this->postJson('/api/demo/login', ['role' => 'buyer'])->assertStatus(503);
    }

    public function test_actions_that_break_demo_for_others_are_locked(): void
    {
        $this->enableDemo();
        $admin = Owner::factory()->admin()->create();
        $other = Owner::factory()->create();
        Sanctum::actingAs($admin);

        $this->postJson('/api/delete-acount')->assertForbidden();
        $this->postJson('/api/send_message', ['email' => 'mine@example.com'])->assertForbidden();
        $this->postJson('/api/update_user', ['name' => 'x'])->assertForbidden();
        $this->postJson("/api/admin/users/{$other->id}/role", ['role' => 'admin'])->assertForbidden();
        $this->postJson("/api/admin/users/{$other->id}/block", ['reason' => 'x'])->assertForbidden();
        $this->deleteJson('/api/admin/settings/hero')->assertForbidden();

        $this->assertDatabaseHas('owners', ['id' => $admin->id, 'deleted_at' => null]);
        $this->assertSame('user', $other->fresh()->role);
        $this->assertNull($other->fresh()->blocked_at);
    }

    /** Остальное в демо работает: модерация, просмотр пользователей */
    public function test_demo_keeps_regular_features(): void
    {
        $this->enableDemo();
        Sanctum::actingAs(Owner::factory()->admin()->create());

        $this->getJson('/api/admin/users')->assertOk();
        $this->getJson('/api/admin/announs')->assertOk();
    }

    /** demo:reset стирает всю базу — вне демо он ничего не трогает */
    public function test_demo_reset_refuses_outside_demo(): void
    {
        $owner = Owner::factory()->create();

        $this->artisan('demo:reset')->assertFailed();
        $this->assertModelExists($owner);
    }
}
