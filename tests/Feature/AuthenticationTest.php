<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $overrides = []): array
    {
        return array_merge(['name' => 'María Torres', 'dni' => '01234567', 'email' => 'maria@example.test', 'password' => 'Acceso1234', 'password_confirmation' => 'Acceso1234'], $overrides);
    }

    public function test_registration_persists_a_hashed_password_and_leading_zero_dni(): void
    {
        $this->post('/registro', $this->data())->assertRedirect('/login')->assertSessionHas('status');
        $user = User::firstOrFail();
        $this->assertSame('01234567', $user->dni);
        $this->assertTrue(Hash::check('Acceso1234', $user->password));
        $this->assertNotSame('Acceso1234', $user->password);
        $this->assertGuest();
    }

    public function test_duplicate_email_and_dni_are_rejected_even_with_uppercase_email(): void
    {
        User::factory()->create(['email' => 'maria@example.test', 'dni' => '01234567']);
        $this->post('/registro', $this->data(['email' => 'MARIA@EXAMPLE.TEST']))->assertSessionHasErrors(['email', 'dni']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_required_fields_dni_format_and_password_confirmation_are_validated(): void
    {
        $this->post('/registro', [])->assertSessionHasErrors(['name', 'dni', 'email', 'password']);
        foreach (['1234567', '123456789', '1234abcd'] as $dni) {
            $this->post('/registro', $this->data(['dni' => $dni]))->assertSessionHasErrors('dni');
        }
        $this->post('/registro', $this->data(['password_confirmation' => 'Otra1234']))->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_weak_password_is_rejected(): void
    {
        $this->post('/registro', $this->data(['password' => 'abcdefgh', 'password_confirmation' => 'abcdefgh']))->assertSessionHasErrors('password');
    }

    public function test_login_authenticates_normalized_email_and_regenerates_session(): void
    {
        $user = User::factory()->create(['email' => 'maria@example.test', 'password' => 'Acceso1234']);
        $this->get('/login');
        $before = session()->getId();
        $this->post('/login', ['email' => 'MARIA@EXAMPLE.TEST', 'password' => 'Acceso1234', 'remember' => 1])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($before, session()->getId());
        $this->assertNotNull($user->fresh()->remember_token);
        $this->get('/dashboard')->assertOk()->assertSee($user->name)->assertSee($user->email)->assertSee($user->dni)->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_incorrect_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'maria@example.test']);
        $this->post('/login', ['email' => 'maria@example.test', 'password' => 'Incorrecta123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_locks_after_five_failures(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'maria@example.test', 'password' => 'Incorrecta123']);
        }
        $this->post('/login', ['email' => 'maria@example.test', 'password' => 'Incorrecta123'])
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('email'));
    }

    public function test_guests_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_logout_invalidates_session_and_rotates_csrf_token(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard');
        $beforeId = session()->getId();
        $beforeToken = session()->token();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->assertNotSame($beforeId, session()->getId());
        $this->assertNotSame($beforeToken, session()->token());
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/logout')->assertStatus(405);
    }

    public function test_pages_render_and_404_is_custom(): void
    {
        foreach (['/login', '/registro', '/olvide-mi-contrasena', '/restablecer-contrasena/test?email=maria@example.test'] as $url) {
            $this->get($url)->assertOk()->assertSee('lang="es"', false);
        }
        $this->get('/pagina-inexistente')->assertNotFound()->assertSee('Página no encontrada');
    }
}
