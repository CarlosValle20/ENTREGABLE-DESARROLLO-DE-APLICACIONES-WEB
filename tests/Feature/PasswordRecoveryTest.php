<?php

namespace Tests\Feature;

use App\Http\Controllers\ForgotPasswordController;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private function resetData(User $user, string $token): array
    {
        return ['email' => $user->email, 'token' => $token, 'password' => 'NuevaClave456', 'password_confirmation' => 'NuevaClave456'];
    }

    public function test_reset_notification_contains_a_real_url_and_only_a_hash_is_stored(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->from('/olvide-mi-contrasena')->post('/olvide-mi-contrasena', ['email' => $user->email])->assertRedirect('/olvide-mi-contrasena')->assertSessionHas('status', ForgotPasswordController::STATUS);
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $stored = DB::table('password_reset_tokens')->where('email', $user->email)->first();
            $this->assertNotSame($notification->token, $stored->token);
            $this->assertTrue(Hash::check($notification->token, $stored->token));
            $mail = $notification->toMail($user);
            $this->assertStringContainsString('/restablecer-contrasena/'.$notification->token, $mail->actionUrl);
            $this->assertStringStartsWith(config('app.url'), $mail->actionUrl);
            $this->get($mail->actionUrl)->assertOk();

            return true;
        });
    }

    public function test_unknown_known_and_throttled_email_have_the_same_response(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        foreach ([$user->email, 'noexiste@example.test', $user->email] as $email) {
            $this->post('/olvide-mi-contrasena', compact('email'))->assertSessionHas('status', ForgotPasswordController::STATUS);
        }
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_valid_token_updates_password_deletes_token_and_revokes_remember_cookie(): void
    {
        $user = User::factory()->create(['password' => 'Antigua123', 'remember_token' => 'old-remember-token']);
        $token = Password::createToken($user);
        $this->post('/restablecer-contrasena', $this->resetData($user, $token))->assertRedirect('/login')->assertSessionHas('status');
        $this->assertTrue(Hash::check('NuevaClave456', $user->fresh()->password));
        $this->assertFalse(Hash::check('Antigua123', $user->fresh()->password));
        $this->assertNotSame('old-remember-token', $user->fresh()->remember_token);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->post('/login', ['email' => $user->email, 'password' => 'Antigua123'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'NuevaClave456'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_token_cannot_be_reused(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $this->post('/restablecer-contrasena', $this->resetData($user, $token))->assertRedirect('/login');
        $this->post('/restablecer-contrasena', $this->resetData($user, $token))->assertSessionHasErrors('email');
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $old = $user->password;
        Password::createToken($user);
        $this->post('/restablecer-contrasena', $this->resetData($user, 'token-invalido'))->assertSessionHasErrors('email');
        $this->assertSame($old, $user->fresh()->password);
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        DB::table('password_reset_tokens')->where('email', $user->email)->update(['created_at' => now()->subMinutes(61)]);
        $this->post('/restablecer-contrasena', $this->resetData($user, $token))->assertSessionHasErrors('email');
    }

    public function test_token_is_bound_to_its_email_and_confirmation_is_required(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $token = Password::createToken($user);
        $this->post('/restablecer-contrasena', $this->resetData($other, $token))->assertSessionHasErrors('email');
        $this->post('/restablecer-contrasena', array_merge($this->resetData($user, $token), ['password_confirmation' => 'Otra123']))->assertSessionHasErrors('password');
    }

    public function test_recovery_endpoint_is_rate_limited(): void
    {
        Notification::fake();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/olvide-mi-contrasena', ['email' => 'unknown@example.test'])->assertRedirect();
        }
        $this->post('/olvide-mi-contrasena', ['email' => 'unknown@example.test'])->assertStatus(429);
    }

    public function test_database_sessions_are_revoked_on_password_reset(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->create();
        DB::table('sessions')->insert(['id' => 'other-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        $token = Password::createToken($user);
        $this->post('/restablecer-contrasena', $this->resetData($user, $token))->assertRedirect('/login');
        $this->assertDatabaseMissing('sessions', ['id' => 'other-session']);
    }

    public function test_actual_log_mailer_writes_recovery_email(): void
    {
        config(['mail.default' => 'log', 'mail.mailers.log.channel' => 'mail']);
        $user = User::factory()->create();
        $this->post('/olvide-mi-contrasena', ['email' => $user->email])->assertSessionHas('status');
        $log = file_get_contents(storage_path('logs/mail.log'));
        $this->assertStringContainsString($user->email, $log);
        $this->assertStringContainsString('/restablecer-contrasena/', $log);
    }
}
