<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Support\RecoveryCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 回復コードでパスワードを再設定する（メールは使わない）
 */
class PasswordResetController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('PasswordReset');
    }

    /**
     * 合えば、パスワードを書き換え、ほかの端末のログインを切り、新しい回復コードを作る。
     * 古い回復コードは、ハッシュを置き換えるので使えなくなる。自動ではログインさせない
     *
     * @throws ValidationException
     */
    public function store(PasswordResetRequest $request): RedirectResponse
    {
        $user = $request->authenticatedUser();
        // そのままの値はこの変数の中にあるだけで、保存するのはハッシュだけ（トランザクションの中）。
        // トランザクションが失敗すれば flash まで進まないので、どこにも残らない
        $recoveryCode = RecoveryCode::generate();

        DB::transaction(function () use ($request, $user, $recoveryCode): void {
            $user->password = $request->string('password')->toString();
            // 「ログインしたまま」のクッキーを、ほかの端末で使えなくする
            $user->setRememberToken(Str::random(60));
            $user->recovery_code_hash = RecoveryCode::hash($recoveryCode);
            $user->save();

            // ほかの端末のログインを切る（セッションをデータベースに置いているときは、その利用者の行を消す）
            if (config('session.driver') === 'database') {
                DB::table(config()->string('session.table', 'sessions'))
                    ->where('user_id', $user->getKey())
                    ->delete();
            }
        });

        Inertia::flash('recoveryCode', RecoveryCode::format($recoveryCode));

        return redirect()->route('recovery-code');
    }
}
