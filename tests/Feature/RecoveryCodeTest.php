<?php

namespace Tests\Feature;

use App\Models\InvitationCode;
use App\Models\User;
use App\Support\RecoveryCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Support\SessionKey;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RecoveryCodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function registration(): array
    {
        return [
            'invitation_code' => InvitationCode::factory()->create()->code,
            'login_id' => 'taro',
            'name' => '太郎',
            'password' => 'correct-horse',
            'password_confirmation' => 'correct-horse',
        ];
    }

    /**
     * 直前の応答が Inertia::flash で入れた回復コード（区切った形）
     */
    private function flashedRecoveryCode(): string
    {
        $flash = session()->get(SessionKey::FLASH_DATA);
        $this->assertIsArray($flash);
        $this->assertIsString($flash['recoveryCode'] ?? null);

        return $flash['recoveryCode'];
    }

    public function test_registering_shows_a_sixteen_letter_recovery_code_in_groups_of_four(): void
    {
        $this->post('/register', $this->registration())->assertRedirect('/recovery-code');

        $shown = $this->flashedRecoveryCode();
        $this->assertMatchesRegularExpression('/\A[a-z0-9]{4}(-[a-z0-9]{4}){3}\z/', $shown);

        $this->get('/recovery-code')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('RecoveryCode')
                ->hasFlash('recoveryCode', $shown)
                ->where('next', ['href' => '/dashboard', 'label' => '次へ']));
    }

    public function test_only_the_hash_of_the_recovery_code_is_stored_in_users(): void
    {
        $this->post('/register', $this->registration());
        $shown = $this->flashedRecoveryCode();
        $plain = RecoveryCode::normalize($shown);

        $row = (array) DB::table('users')->where('login_id', 'taro')->first();
        $hash = $row['recovery_code_hash'];
        $this->assertIsString($hash);
        $this->assertTrue(Hash::check($plain, $hash));

        foreach ($row as $column => $value) {
            $text = is_scalar($value) ? (string) $value : '';
            $this->assertStringNotContainsString($plain, $text, "users.{$column} にそのままの値がある");
            $this->assertStringNotContainsString($shown, $text, "users.{$column} に区切った値がある");
        }
    }

    public function test_the_recovery_code_is_not_readable_in_the_sessions_table(): void
    {
        // 本番と同じく、セッションをデータベースに置く（テストの既定は array）
        config(['session.driver' => 'database']);
        $this->app->make('session')->forgetDrivers();
        $this->assertTrue(config('session.encrypt'), 'SESSION_ENCRYPT が true になっていない');

        $this->post('/register', $this->registration());
        $shown = $this->flashedRecoveryCode();

        $payloads = DB::table('sessions')->pluck('payload');
        $this->assertNotEmpty($payloads);

        foreach ($payloads as $payload) {
            // データベースのセッションは base64 にして保存される。戻した中身に、そのままの値が無いことを見る
            $this->assertIsString($payload);
            $decoded = base64_decode($payload);
            $this->assertStringNotContainsString($shown, $decoded);
            $this->assertStringNotContainsString(RecoveryCode::normalize($shown), $decoded);
        }
    }

    public function test_reloading_the_recovery_code_page_no_longer_shows_the_code(): void
    {
        $this->post('/register', $this->registration());
        $this->get('/recovery-code')->assertInertia(fn (Assert $page) => $page->hasFlash('recoveryCode'));

        $this->get('/recovery-code')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('RecoveryCode')
                ->missingFlash('recoveryCode'));
    }

    public function test_the_recovery_code_page_leads_guests_to_the_login_page(): void
    {
        $this->get('/recovery-code')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('next', ['href' => '/login', 'label' => 'ログインの画面へ']));
    }

    public function test_formatting_and_normalizing_are_inverse(): void
    {
        $code = RecoveryCode::generate();

        $this->assertMatchesRegularExpression('/\A[a-z0-9]{16}\z/', $code);
        $this->assertSame($code, RecoveryCode::normalize(RecoveryCode::format($code)));
        $this->assertSame($code, RecoveryCode::normalize(strtoupper(RecoveryCode::format($code))));
    }

    public function test_a_user_can_be_made_with_a_known_recovery_code(): void
    {
        $user = User::factory()->withRecoveryCode('abcdabcdabcdabcd')->create();

        $this->assertIsString($user->recovery_code_hash);
        $this->assertTrue(Hash::check('abcdabcdabcdabcd', $user->recovery_code_hash));
        $this->assertArrayNotHasKey('recovery_code_hash', $user->toArray());
    }
}
