<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_rendered_for_guest(): void
    {
        $this->get('/login')
            ->assertInertia(fn (Assert $page) => $page
                ->component('AuthPage')
                ->where('mode', 'login')
                ->where('auth.user', null));
    }

    public function test_guest_is_redirected_to_login_from_workspace(): void
    {
        $this->get('/calendar')->assertRedirect('/login');
    }

    public function test_valid_credentials_start_session_and_redirect_to_calendar(): void
    {
        $user = User::factory()->create([
            'email' => 'anna@example.com',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => 'anna@example.com',
            'password' => 'password',
            'remember' => true,
        ])->assertRedirect('/calendar');

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_return_visible_validation_error(): void
    {
        User::factory()->create([
            'email' => 'anna@example.com',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => 'anna@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors([
            'email' => 'Неверный email или пароль.',
        ]);

        $this->assertGuest();
    }

    public function test_registration_creates_employee_and_starts_session(): void
    {
        $this->post('/register', [
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/calendar');

        $user = User::where('email', 'anna@example.com')->firstOrFail();
        $this->assertSame('employee', $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_ends_session_and_redirects_to_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_authenticated_user_receives_calendar_page_props(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/calendar')
            ->assertInertia(fn (Assert $page) => $page
                ->component('CalendarPage')
                ->where('auth.user.id', $user->id)
                ->has('appointments')
                ->has('stats'));
    }

    public function test_employee_cannot_open_administration_pages(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)->get('/users')->assertForbidden();
        $this->actingAs($employee)->get('/history')->assertForbidden();
    }

    public function test_admin_receives_users_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/users')
            ->assertInertia(fn (Assert $page) => $page
                ->component('UsersPage')
                ->has('users', 1));
    }

    public function test_authenticated_user_can_create_client(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/clients', [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'phone' => '+79990000000',
        ])->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'created_by' => $user->id,
        ]);
    }
}
