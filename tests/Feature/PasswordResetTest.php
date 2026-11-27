<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RecoveryCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ViewErrorBag;
use Inertia\Support\SessionKey;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** テストのための回復コード（区切りの無い形） */
    private const CODE = 'abcd1234efgh5678';

    private function makeUser(string $loginId = 'taro'): User
    {
        return User::factory()
            ->withRecoveryCode(self::CODE)
            ->create(['login_id' => $loginId, 'password' => 'old-password']);
    }

    /**
     * @param  array<string, string>  $override
     * @return array<string, string>
     */
    private function input(array $override = []): array
    {
        return [
            'login_id' => 'taro',
            'recovery_code' => self::CODE,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            ...$override,
        ];
    }

    private function firstError(string $field): string
    {
        $errors = session('errors');
        $this->assertInstanceOf(ViewErrorBag::class, $errors);

        return (string) $errors->first($field);
    }

    public function test_reset_page_is_the_password_reset_inertia_page(): void
    {
        $this->get('/password/reset')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('PasswordReset'));
    }

    public function test_a_user_can_reset_the_password_with_the_recovery_code(): void
    {
        $user = $this->makeUser();

        $this->post('/password/reset', $this->input())->assertRedirect('/recovery-code');

        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertFalse(Hash::check('old-password', $user->password));

        $this->post('/login', ['login_id' => 'taro', 'password' => 'old-password'])->assertSessionHasErrors('login_id');
        $this->assertGuest();
        $this->post('/login', ['login_id' => 'taro', 'password' => 'new-password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_recovery_code_with_hyphens_and_capitals_also_works(): void
    {
        $user = $this->makeUser();

        $this->post('/password/reset', $this->input(['recovery_code' => 'ABCD-1234-EFGH-5678']))
            ->assertRedirect('/recovery-code');

        $this->assertTrue(Hash::check('new-password', (string) $user->fresh()?->password));
    }

    public function test_a_new_recovery_code_is_shown_and_the_old_one_no_longer_works(): void
    {
        $user = $this->makeUser();

        $this->post('/password/reset', $this->input());

        $flash = session()->get(SessionKey::FLASH_DATA);
        $this->assertIsArray($flash);
        $this->assertIsString($flash['recoveryCode'] ?? null);
        $newCode = RecoveryCode::normalize($flash['recoveryCode']);
        $this->assertNotSame(self::CODE, $newCode);
        $this->assertTrue(Hash::check($newCode, (string) $user->fresh()?->recovery_code_hash));

        // 新しい回復コードの画面が出る（ログインしていないので、行き先はログインの画面）
        $this->get('/recovery-code')
            ->assertInertia(fn (Assert $page) => $page
                ->component('RecoveryCode')
                ->hasFlash('recoveryCode', $flash['recoveryCode'])
                ->where('next', ['href' => '/login', 'label' => 'ログインの画面へ']));

        // 古い回復コードはもう使えない
        $this->post('/password/reset', $this->input(['password' => 'other-password', 'password_confirmation' => 'other-password']))
            ->assertSessionHasErrors(['login_id' => 'ログインIDか回復コードが違います。']);
        $this->assertTrue(Hash::check('new-password', (string) $user->fresh()?->password));
    }

    public function test_resetting_signs_out_the_other_devices(): void
    {
        config(['session.driver' => 'database']);
        $this->app->make('session')->forgetDrivers();
        $user = $this->makeUser();
        $user->setRememberToken('remember-token-before');
        $user->save();
        DB::table('sessions')->insert([
            'id' => 'other-device-session',
            'user_id' => $user->id,
            'payload' => '',
            'last_activity' => time(),
        ]);
        DB::table('sessions')->insert([
            'id' => 'someone-else-session',
            'user_id' => User::factory()->create()->id,
            'payload' => '',
            'last_activity' => time(),
        ]);

        $this->post('/password/reset', $this->input())->assertRedirect('/recovery-code');

        $this->assertFalse(DB::table('sessions')->where('id', 'other-device-session')->exists());
        $this->assertTrue(DB::table('sessions')->where('id', 'someone-else-session')->exists());
        $this->assertNotSame('remember-token-before', $user->fresh()?->getRememberToken());
    }

    public function test_resetting_does_not_log_in_and_the_login_page_tells_to_use_the_new_password(): void
    {
        $this->makeUser();

        $this->post('/password/reset', $this->input())->assertRedirect('/recovery-code');
        $this->assertGuest();

        $this->get('/recovery-code');
        $this->get('/login')->assertInertia(fn (Assert $page) => $page
            ->component('Login')
            ->where('status', 'パスワードを再設定しました。新しいパスワードでログインしてください。'));
        $this->assertGuest();
    }

    public function test_unknown_login_id_and_wrong_recovery_code_give_the_same_error(): void
    {
        $this->makeUser();
        $message = 'ログインIDか回復コードが違います。';

        // セッションの誤りは次の要求で入れ替わるので、要求ごとに、その直後に確かめる
        $this->post('/password/reset', $this->input(['login_id' => 'nobody']))
            ->assertSessionHasErrors(['login_id' => $message]);

        $this->post('/password/reset', $this->input(['recovery_code' => 'zzzz-zzzz-zzzz-zzzz']))
            ->assertSessionHasErrors(['login_id' => $message]);
    }

    public function test_an_unknown_login_id_still_runs_one_hash_check(): void
    {
        Hash::partialMock()->shouldReceive('check')->once()->andReturnFalse();

        $this->post('/password/reset', $this->input(['login_id' => 'nobody']))
            ->assertSessionHasErrors('login_id');
    }

    public function test_a_rejected_password_changes_neither_the_password_nor_the_recovery_code(): void
    {
        $user = $this->makeUser();
        $before = $user->only(['password', 'recovery_code_hash', 'remember_token']);

        $this->post('/password/reset', $this->input(['password_confirmation' => 'other-password']))
            ->assertSessionHasErrors(['password' => 'パスワードが確認の欄と一致しません。']);
        $this->post('/password/reset', $this->input(['password' => 'short', 'password_confirmation' => 'short']))
            ->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入れてください。']);

        $this->assertSame($before, $user->fresh()?->only(['password', 'recovery_code_hash', 'remember_token']));
    }

    public function test_the_sixth_attempt_for_a_login_id_is_refused_even_with_the_right_code(): void
    {
        $user = $this->makeUser();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/password/reset', $this->input(['recovery_code' => 'wrong-code-'.$i]));
        }

        $this->post('/password/reset', $this->input())->assertSessionHasErrors('login_id');
        $this->assertMatchesRegularExpression(
            '/\A再設定の失敗が続きました。\d+秒後にもう一度お試しください。\z/u',
            $this->firstError('login_id'),
        );
        $this->assertTrue(Hash::check('old-password', (string) $user->fresh()?->password));
    }

    public function test_twenty_failures_from_one_ip_refuse_other_login_ids(): void
    {
        $user = $this->makeUser();

        for ($i = 0; $i < 20; $i++) {
            $this->post('/password/reset', $this->input(['login_id' => 'nobody'.$i]));
        }

        $this->post('/password/reset', $this->input())->assertSessionHasErrors('login_id');
        $this->assertMatchesRegularExpression('/\A再設定の失敗が続きました。/u', $this->firstError('login_id'));
        $this->assertTrue(Hash::check('old-password', (string) $user->fresh()?->password));
    }

    public function test_logged_in_users_cannot_open_the_reset_page(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/password/reset')->assertRedirect('/dashboard');
        $this->actingAs($user)->post('/password/reset', $this->input())->assertRedirect('/dashboard');
        $this->assertTrue(Hash::check('old-password', (string) $user->fresh()?->password));
    }
}
