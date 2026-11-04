<?php

namespace Tests\Feature;

use App\Events\MyEvent;
use App\Events\NewChatMessage;
use App\Models\Owner;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\RefreshTestDatabase;
use Tests\TestCase;

/** Websocket: подписаться на переписку и уведомления может только их участник (routes/channels.php) */
class PrivateChannelsTest extends TestCase
{
    use RefreshTestDatabase;

    private function subscribe(string $channel)
    {
        return $this->postJson('/api/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => $channel]);
    }

    public function test_guest_cannot_subscribe(): void
    {
        $this->subscribe('private-user.1')->assertStatus(401);
    }

    public function test_user_subscribes_only_to_own_notifications(): void
    {
        [$me, $other] = Owner::factory()->count(2)->create();
        Sanctum::actingAs($me);

        $this->subscribe("private-user.{$me->id}")->assertOk()->assertJsonStructure(['auth']);
        $this->subscribe("private-user.{$other->id}")->assertForbidden();
    }

    public function test_only_participants_subscribe_to_chat(): void
    {
        [$a, $b, $stranger] = Owner::factory()->count(3)->create();
        $chat = 'private-chat.' . min($a->id, $b->id) . '.' . max($a->id, $b->id);

        Sanctum::actingAs($a);
        $this->subscribe($chat)->assertOk();
        Sanctum::actingAs($b);
        $this->subscribe($chat)->assertOk();
        Sanctum::actingAs($stranger);
        $this->subscribe($chat)->assertForbidden();
    }

    public function test_blocked_user_cannot_subscribe(): void
    {
        $me = Owner::factory()->blocked()->create();
        Sanctum::actingAs($me);

        $this->subscribe("private-user.{$me->id}")->assertForbidden();
    }

    public function test_chat_message_goes_only_to_private_channels(): void
    {
        Event::fake([MyEvent::class, NewChatMessage::class]);
        [$sender, $recipient] = Owner::factory()->count(2)->create();
        Sanctum::actingAs($sender);

        $this->postJson('/api/send-message', ['text' => 'Здравствуйте', 'friendId' => $recipient->id, 'type' => 'message', 'time' => '12:00'])
            ->assertCreated();

        $chat = 'private-chat.' . min($sender->id, $recipient->id) . '.' . max($sender->id, $recipient->id);
        Event::assertDispatched(MyEvent::class, fn ($e) => $e->broadcastOn()->name === $chat);
        Event::assertDispatched(NewChatMessage::class, fn ($e) => $e->broadcastOn()[0]->name === "private-user.{$recipient->id}");
    }

    /** Фото и видео — только через send-file / send-video, иначе можно подложить имя чужого файла */
    public function test_send_message_rejects_file_types(): void
    {
        [$sender, $recipient] = Owner::factory()->count(2)->create();
        Sanctum::actingAs($sender);

        $this->postJson('/api/send-message', ['text' => 'chat-photo-of-someone.webp', 'friendId' => $recipient->id, 'type' => 'picture', 'time' => '12:00'])
            ->assertStatus(422)->assertJsonValidationErrors('type');
    }
}
