<?php

namespace Tests\Feature;

use App\Models\InvitationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validInput(InvitationCode $invitation): array
    {
        return [
            'invitation_code' => $invitation->code,
            'login_id' => 'taro',
            'name' => '太郎',
            'password' => 'correct-horse',
            'password_confirmation' => 'correct-horse',
        ];
    }

    public function test_register_page_is_the_register_inertia_page(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Register'));
    }

    public function test_a_new_user_can_register(): void
    {
        $invitation = InvitationCode::factory()->create();

        $response = $this->post('/register', [
            ...$this->validInput($invitation),
            'login_id' => 'TaRo_01',
        ]);

        $response->assertRedirect('/dashboard');
        $user = User::query()->sole();
        $this->assertSame('taro_01', $user->login_id);
        $this->assertSame('太郎', $user->name);
        $this->assertAuthenticatedAs($user);

        $invitation->refresh();
        $this->assertNotNull($invitation->used_at);
        $this->assertSame($user->id, $invitation->user_id);
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidInputs(): array
    {
        return [
            '使えない文字' => [['login_id' => 'taro!'], 'login_id', 'ログインIDは、半角の英小文字・数字・_・-で入れてください。'],
            '全角の文字' => [['login_id' => 'ｔａｒｏ'], 'login_id', 'ログインIDは、半角の英小文字・数字・_・-で入れてください。'],
            'ログインID が短い' => [['login_id' => 'ab'], 'login_id', 'ログインIDは3文字以上で入れてください。'],
            'ログインID が長い' => [['login_id' => str_repeat('a', 31)], 'login_id', 'ログインIDは30文字以内で入れてください。'],
            '表示の名前が空' => [['name' => '   '], 'name', '表示の名前を入れてください。'],
            '表示の名前が長い' => [['name' => str_repeat('あ', 51)], 'name', '表示の名前は50文字以内で入れてください。'],
            'パスワードが一致しない' => [['password_confirmation' => 'other-horse'], 'password', 'パスワードが確認の欄と一致しません。'],
            'パスワードが短い' => [['password' => 'short', 'password_confirmation' => 'short'], 'password', 'パスワードは8文字以上で入れてください。'],
            '存在しない招待コード' => [['invitation_code' => 'nosuchcode00'], 'invitation_code', 'この招待コードは使えません。'],
        ];
    }

    /**
     * @param  array<string, string>  $override
     */
    #[DataProvider('invalidInputs')]
    public function test_registration_fails_with_a_japanese_error(array $override, string $field, string $message): void
    {
        $invitation = InvitationCode::factory()->create();

        $this->post('/register', [...$this->validInput($invitation), ...$override])
            ->assertSessionHasErrors([$field => $message]);

        $this->assertSame(0, User::query()->count());
        $this->assertNull($invitation->fresh()?->used_at);
    }

    public function test_registration_fails_when_the_login_id_differs_only_in_case(): void
    {
        User::factory()->create(['login_id' => 'taro']);
        $invitation = InvitationCode::factory()->create();

        $this->post('/register', [...$this->validInput($invitation), 'login_id' => 'Taro'])
            ->assertSessionHasErrors(['login_id' => 'このログインIDはもう使われています。']);

        $this->assertSame(1, User::query()->count());
        $this->assertNull($invitation->fresh()?->used_at);
    }

    public function test_registration_fails_with_a_used_invitation_code(): void
    {
        $invitation = InvitationCode::factory()->used()->create();
        $usedAt = $invitation->used_at;
        $countBefore = User::query()->count();

        $this->post('/register', $this->validInput($invitation))
            ->assertSessionHasErrors(['invitation_code' => 'この招待コードは使えません。']);

        $this->assertSame($countBefore, User::query()->count());
        $this->assertEquals($usedAt, $invitation->fresh()?->used_at);
    }

    public function test_the_invitation_code_stays_unused_when_creating_the_user_fails(): void
    {
        $invitation = InvitationCode::factory()->create();
        // 利用者を作る直前に失敗させる
        User::creating(function (): void {
            throw new RuntimeException('利用者を作るのに失敗した（テストのため）');
        });

        $this->post('/register', $this->validInput($invitation))->assertServerError();

        $this->assertSame(0, User::query()->count());
        $this->assertNull($invitation->fresh()?->used_at);
    }

    public function test_the_user_is_rolled_back_when_marking_the_invitation_code_fails(): void
    {
        $invitation = InvitationCode::factory()->create();
        // 利用者を作った後、招待コードを使用済みにするところで失敗させる（トランザクションの巻き戻りを見る）
        InvitationCode::updating(function (): void {
            throw new RuntimeException('招待コードを使用済みにするのに失敗した（テストのため）');
        });

        $this->post('/register', $this->validInput($invitation))->assertServerError();

        $this->assertSame(0, User::query()->count());
        $this->assertNull($invitation->fresh()?->used_at);
    }

    public function test_a_duplicate_login_id_from_the_database_becomes_a_japanese_error(): void
    {
        $invitation = InvitationCode::factory()->create();
        // 入力の確かめの後、利用者を作る直前に、同じログインID の行が先に入った場合を作る
        User::creating(function (User $user): void {
            DB::table('users')->insert([
                'name' => '先に入った人',
                'login_id' => $user->login_id,
                'share_id' => User::newUniqueShareId(),
                'password' => 'x',
            ]);
        });

        $this->post('/register', $this->validInput($invitation))
            ->assertSessionHasErrors(['login_id' => 'このログインIDはもう使われています。']);

        $this->assertNull($invitation->fresh()?->used_at);
    }
}
