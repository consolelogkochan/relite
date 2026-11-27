<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 回復コードの画面。登録の後（ログインしている）と、再設定の後（ログインしていない）の両方から来る。
 * 値そのものは、前の要求が Inertia::flash で入れたもので、この1回の応答で消える（読み込み直すと見られない）
 */
class RecoveryCodeController extends Controller
{
    public function show(Request $request): Response
    {
        $loggedIn = $request->user() !== null;

        // 再設定の後に値を見せるときは、次のログインの画面に知らせを出す（セッションの flash は次の要求まで残る）
        if (! $loggedIn && array_key_exists('recoveryCode', Inertia::getFlashed($request))) {
            $request->session()->flash('status', __('auth.reset_done'));
        }

        return Inertia::render('RecoveryCode', [
            'next' => $loggedIn
                ? ['href' => route('dashboard', absolute: false), 'label' => '次へ']
                : ['href' => route('login', absolute: false), 'label' => 'ログインの画面へ'],
        ]);
    }
}
