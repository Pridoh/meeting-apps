<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_open_the_login_form_and_is_redirected_from_the_dashboard(): void
    {
        $this->withoutVite();

        $this->get('/login')->assertOk();

        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->assertSame(url('/login'), route('login'));
        $this->assertGuest();
    }

    public function test_login_requires_an_email_and_a_password(): void
    {
        $this->from('/login')->post('/login', [])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_incorrect_credentials_leave_the_session_unauthenticated(): void
    {
        $user = User::factory()->create();

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_sign_in_view_the_dashboard_and_sign_out(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirectToRoute('dashboard');

        $this->assertSame(url('/dashboard'), route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk();

        $this->post('/logout')->assertRedirectToRoute('login');

        $this->assertGuest();
        $this->get('/dashboard')->assertRedirectToRoute('login');
    }

    public function test_fifth_attempt_can_sign_in_after_four_failures(): void
    {
        $this->freezeSecond();
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $this->from('/login')->post('/login', [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])->assertRedirect('/login')->assertSessionHasErrors('email');
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirectToRoute('dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_retries_after_another_request_releases_the_attempt_lock(): void
    {
        $user = User::factory()->create(['email' => 'organizer@example.test']);
        $address = '192.0.2.1';
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $this->withServerVariables(['REMOTE_ADDR' => $address]);
        $key = 'login:'.hash('sha256', $user->email.'|'.$address).':attempt';
        $lock = Cache::lock($key, 30);
        $this->assertTrue($lock->get());

        try {
            $this->from('/login')->post('/login', $credentials)
                ->assertRedirect('/login')
                ->assertSessionHasErrors(['email' => 'Percobaan masuk sedang diproses. Coba lagi sebentar.']);
            $this->assertGuest();
        } finally {
            $lock->release();
        }

        $this->post('/login', $credentials)->assertRedirectToRoute('dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_five_failures_block_the_email_and_ip_pair_for_one_minute(): void
    {
        $this->freezeSecond();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $firstAddress = ['REMOTE_ADDR' => '192.0.2.1'];
        $secondAddress = ['REMOTE_ADDR' => '192.0.2.2'];
        $credentials = ['email' => $user->email, 'password' => 'password'];

        $this->withServerVariables($firstAddress);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/login')->post('/login', [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])->assertRedirect('/login')->assertSessionHasErrors('email');
        }

        $this->from('/login')->post('/login', $credentials)
            ->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', [
            'email' => $otherUser->email,
            'password' => 'password',
        ])->assertRedirectToRoute('dashboard');
        $this->assertAuthenticatedAs($otherUser);
        $this->post('/logout')->assertRedirectToRoute('login');

        $this->withServerVariables($secondAddress)
            ->post('/login', $credentials)
            ->assertRedirectToRoute('dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirectToRoute('login');

        $this->withServerVariables($firstAddress);
        $this->travel(59)->seconds();
        $this->from('/login')->post('/login', $credentials)
            ->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->travel(1)->second();
        $this->post('/login', $credentials)->assertRedirectToRoute('dashboard');

        $this->assertAuthenticatedAs($user);
    }
}
