<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\InvitationCode;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Register');
    }

    /**
     * 利用者を作ることと招待コードを使用済みにすることを、1つのトランザクションで行う
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            // 招待コードは「すでにある行」なので、鍵を掛けて読み直す。
            // 同じコードで同時に登録しようとした2人目は、1人目の確定を待ってから used_at を見る。
            // 使えないコードはふつう RegisterRequest で断る。ここは同時に使われたときの最後の関所（二重に守る）
            $invitation = InvitationCode::query()
                ->where('code', $request->string('invitation_code')->toString())
                ->lockForUpdate()
                ->first();

            if ($invitation === null || $invitation->isUsed()) {
                throw ValidationException::withMessages([
                    'invitation_code' => __('auth.invitation_unusable'),
                ]);
            }

            try {
                $user = User::create([
                    'name' => $request->string('name')->toString(),
                    'login_id' => $request->string('login_id')->toString(),
                    'password' => $request->string('password')->toString(),
                ]);
            } catch (UniqueConstraintViolationException $e) {
                // 入力の確かめの後、ほぼ同時の登録で同じログインID が先に入ったとき
                if (str_contains($e->getMessage(), 'users_login_id_unique')) {
                    throw ValidationException::withMessages([
                        'login_id' => __('validation.custom.login_id.unique'),
                    ]);
                }

                throw $e;
            }

            $invitation->used_at = now();
            $invitation->user_id = $user->id;
            $invitation->save();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
