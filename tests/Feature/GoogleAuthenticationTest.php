<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function enableGoogle(): void
    {
        config(['services.google.client_id' => 'test-client', 'services.google.client_secret' => 'test-secret']);
    }

    private function profile(string $email, bool $verified = true): void
    {
        $profile = (new GoogleUser)->setRaw(['email_verified' => $verified])->map(['id' => 'google-subject-123', 'email' => $email, 'name' => 'María Torres']);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($profile);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
    }

    public function test_missing_credentials_return_a_clear_error_without_simulated_login(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);
        $this->get('/auth/google')->assertRedirect('/login')->assertSessionHasErrors('google');
        $this->assertGuest();
    }

    public function test_redirect_uses_state_and_google_authorization_endpoint(): void
    {
        $this->enableGoogle();
        $response = $this->get('/auth/google')->assertRedirect();
        $this->assertStringStartsWith('https://accounts.google.com/', $response->headers->get('Location'));
        $this->assertStringContainsString('state=', $response->headers->get('Location'));
        $response->assertSessionHas('state')->assertSessionHas('google_purpose', 'login');
    }

    public function test_matching_email_does_not_automatically_link_accounts(): void
    {
        $this->enableGoogle();
        $user = User::factory()->create();
        $this->profile($user->email);
        $this->withSession(['google_purpose' => 'login'])->get('/auth/google/callback')->assertRedirect('/login')->assertSessionHasErrors('google');
        $this->assertNull($user->fresh()->google_id);
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_link_requires_current_password(): void
    {
        $this->enableGoogle();
        $user = User::factory()->create(['password' => 'Acceso1234']);
        $this->actingAs($user)->post('/auth/google/vincular', ['current_password' => 'Incorrecta123'])->assertSessionHasErrors('current_password');
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_password_confirmed_link_sets_oauth_intent(): void
    {
        $this->enableGoogle();
        $user = User::factory()->create(['password' => 'Acceso1234']);
        $this->actingAs($user)->post('/auth/google/vincular', ['current_password' => 'Acceso1234'])
            ->assertRedirect()->assertSessionHas('google_purpose', 'link')->assertSessionHas('google_link_user', $user->id);
    }

    public function test_explicit_link_with_same_verified_email_is_allowed(): void
    {
        $this->enableGoogle();
        $user = User::factory()->create();
        $this->profile($user->email);
        $this->actingAs($user)->withSession(['google_purpose' => 'link', 'google_link_user' => $user->id])
            ->get('/auth/google/callback')->assertRedirect('/dashboard')->assertSessionHas('status');
        $this->assertSame('google-subject-123', $user->fresh()->google_id);
    }

    public function test_linked_google_identity_can_sign_in(): void
    {
        $this->enableGoogle();
        $user = User::factory()->create();
        $user->forceFill(['google_id' => 'google-subject-123'])->save();
        $this->profile($user->email);
        $this->withSession(['google_purpose' => 'login'])->get('/auth/google/callback')->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_unverified_email_and_different_email_cannot_be_linked(): void
    {
        $this->enableGoogle();
        $user = User::factory()->create();
        $this->profile('other@example.test', false);
        $this->actingAs($user)->withSession(['google_purpose' => 'link', 'google_link_user' => $user->id])
            ->get('/auth/google/callback')->assertSessionHasErrors('google');
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_google_link_cannot_use_different_verified_email(): void
    {
        $this->enableGoogle();
        $user = User::factory()->create();
        $this->profile('other@example.test');
        $this->actingAs($user)->withSession(['google_purpose' => 'link', 'google_link_user' => $user->id])
            ->get('/auth/google/callback')->assertSessionHasErrors('google');
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_invalid_state_is_rejected_by_real_provider_before_remote_call(): void
    {
        $this->enableGoogle();
        $this->withSession(['google_purpose' => 'login', 'state' => 'expected-state'])
            ->get('/auth/google/callback?state=wrong-state')->assertRedirect('/login')->assertSessionHasErrors('google');
        $this->assertGuest();
    }

    public function test_callback_requires_an_initiated_flow(): void
    {
        $this->enableGoogle();
        $this->get('/auth/google/callback')->assertRedirect('/login')->assertSessionHasErrors('google');
        $this->assertGuest();
    }
}
