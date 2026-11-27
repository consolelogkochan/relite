<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ViewErrorBag;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create(['login_id' => 'taro', 'password' => 'correct-horse']);
    }

    public function test_login_page_is_the_login_inertia_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Login'));
    }

    public function test_a_user_can_log_in_with_the_login_id(): void
    {
        $user = $this->makeUser();

        $this->post('/login', ['login_id' => 'TARO', 'password' => 'correct-horse'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_remember_me_sets_the_remember_cookie(): void
    {
        $user = $this->makeUser();

        $response = $this->post('/login', ['login_id' => 'taro', 'password' => 'correct-horse', 'remember' => true]);

        $response->assertCookie(auth()->guard('web')->getRecallerName());
        $this->assertNotNull($user->fresh()?->remember_token);
    }

    public function test_without_remember_me_there_is_no_remember_cookie(): void
    {
        $this->makeUser();

        $this->post('/login', ['login_id' => 'taro', 'password' => 'correct-horse'])
            ->assertCookieMissing(auth()->guard('web')->getRecallerName());
    }

    public function test_a_user_can_log_out(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_unknown_login_id_and_wrong_password_give_the_same_error(): void
    {
        $this->makeUser();

        $message = 'ログインIDかパスワードが違います。';

        // セッションの誤りは次の要求で入れ替わるので、要求ごとに、その直後に確かめる
        $this->post('/login', ['login_id' => 'nobody', 'password' => 'correct-horse'])
            ->assertSessionHasErrors(['login_id' => $message]);

        $this->post('/login', ['login_id' => 'taro', 'password' => 'wrong-horse'])
            ->assertSessionHasErrors(['login_id' => $message]);

        $this->assertGuest();
    }

    public function test_the_sixth_attempt_is_refused_even_with_the_right_password(): void
    {
        $this->makeUser();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['login_id' => 'taro', 'password' => 'wrong-horse']);
        }

        $response = $this->post('/login', ['login_id' => 'taro', 'password' => 'correct-horse']);

        $response->assertSessionHasErrors('login_id');
        $errors = session('errors');
        $this->assertInstanceOf(ViewErrorBag::class, $errors);
        $error = $errors->first('login_id');
        $this->assertMatchesRegularExpression('/\Aログインの失敗が続きました。\d+秒後にもう一度お試しください。\z/u', $error);
        $this->assertGuest();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_logged_in_users_are_sent_to_the_dashboard_from_login_and_register(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/login')->assertRedirect('/dashboard');
        $this->actingAs($user)->get('/register')->assertRedirect('/dashboard');
    }

    public function test_dashboard_is_the_dashboard_inertia_page_with_only_the_name(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('auth.user', ['name' => $user->name]));
    }

    public function test_welcome_is_the_welcome_inertia_page_without_a_user(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('auth.user', null));
    }

    public function test_an_unknown_login_id_still_runs_one_hash_check(): void
    {
        Hash::partialMock()->shouldReceive('check')->once()->andReturnFalse();

        $this->post('/login', ['login_id' => 'nobody', 'password' => 'correct-horse'])
            ->assertSessionHasErrors('login_id');
    }

    public function test_twenty_failures_from_one_ip_refuse_other_login_ids(): void
    {
        $this->makeUser();

        for ($i = 0; $i < 20; $i++) {
            $this->post('/login', ['login_id' => 'nobody'.$i, 'password' => 'wrong-horse']);
        }

        $this->post('/login', ['login_id' => 'taro', 'password' => 'correct-horse'])
            ->assertSessionHasErrors('login_id');
        $errors = session('errors');
        $this->assertInstanceOf(ViewErrorBag::class, $errors);
        $this->assertMatchesRegularExpression('/\Aログインの失敗が続きました。/u', (string) $errors->first('login_id'));
        $this->assertGuest();
    }
}
