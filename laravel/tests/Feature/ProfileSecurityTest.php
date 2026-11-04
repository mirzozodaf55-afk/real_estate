<?php

namespace Tests\Feature;

use App\Mail\LoginCodeMail;
use App\Models\Owner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\RefreshTestDatabase;
use Tests\TestCase;

/** Смена почты (логина) и загрузка аватара */
class ProfileSecurityTest extends TestCase
{
    use RefreshTestDatabase;

    private Owner $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->owner = Owner::factory()->create(['email' => 'old@example.com']);
        Sanctum::actingAs($this->owner);
    }

    private function sentCode(): string
    {
        $code = null;
        Mail::assertSent(LoginCodeMail::class, function ($mail) use (&$code) {
            $code = $mail->code;
            return $mail->hasTo('new@example.com') && $mail->emailChange;
        });
        return $code;
    }

    /** Без кода можно было записать себе чужую почту и войти в аккаунт её хозяина, когда тот зарегистрируется */
    public function test_email_changes_only_after_code_from_new_address(): void
    {
        $this->postJson('/api/send_message', ['email' => 'new@example.com'])->assertOk()->assertJson(['pending' => true]);
        $this->assertSame('old@example.com', $this->owner->fresh()->email);

        $this->postJson('/api/send_message', ['email' => 'new@example.com', 'code' => $this->sentCode()])->assertOk();
        $this->assertSame('new@example.com', $this->owner->fresh()->email);
    }

    public function test_wrong_code_does_not_change_email(): void
    {
        $this->postJson('/api/send_message', ['email' => 'new@example.com'])->assertOk();
        $wrong = $this->sentCode() === '000000' ? '111111' : '000000';

        $this->postJson('/api/send_message', ['email' => 'new@example.com', 'code' => $wrong])->assertStatus(422);
        $this->assertSame('old@example.com', $this->owner->fresh()->email);
    }

    /** Код от одной почты не подходит к другой */
    public function test_code_is_bound_to_requested_address(): void
    {
        $this->postJson('/api/send_message', ['email' => 'new@example.com'])->assertOk();

        $this->postJson('/api/send_message', ['email' => 'other@example.com', 'code' => $this->sentCode()])->assertStatus(422);
        $this->assertSame('old@example.com', $this->owner->fresh()->email);
    }

    public function test_code_cannot_be_brute_forced(): void
    {
        $this->postJson('/api/send_message', ['email' => 'new@example.com'])->assertOk();
        $code = $this->sentCode();
        $wrong = $code === '000000' ? '111111' : '000000';

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/send_message', ['email' => 'new@example.com', 'code' => $wrong])->assertStatus(422);
        }
        $this->postJson('/api/send_message', ['email' => 'new@example.com', 'code' => $code])->assertStatus(429);
        $this->assertSame('old@example.com', $this->owner->fresh()->email);
    }

    public function test_cannot_take_email_of_another_account(): void
    {
        Owner::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/send_message', ['email' => 'taken@example.com'])->assertStatus(422);
        Mail::assertNothingSent();
    }

    public function test_avatar_must_be_an_image(): void
    {
        $file = UploadedFile::fake()->createWithContent('avatar.html', '<script>alert(1)</script>');

        $this->post('/api/avatar', ['avatar' => $file], ['Accept' => 'application/json'])->assertStatus(422);
    }

    /** Расширение — по содержимому файла: картинка с именем .html не станет страницей на нашем домене */
    public function test_avatar_extension_comes_from_content_not_name(): void
    {
        // UploadedFile::fake() берёт тип из имени файла — нужен настоящий PNG под чужим именем
        $path = tempnam(sys_get_temp_dir(), 'avatar');
        imagepng(imagecreatetruecolor(800, 500), $path);
        $file = new UploadedFile($path, 'avatar.html', null, null, true);

        $name = $this->post('/api/avatar', ['avatar' => $file], ['Accept' => 'application/json'])->assertOk()->json('avatar');
        try {
            $this->assertStringEndsWith('.png', $name);
            $this->assertSame($name, $this->owner->fresh()->avatar);
        } finally {
            @unlink(public_path('avatar/' . $name));
        }
    }
}
