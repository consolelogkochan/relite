<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\FailureLimiter;
use App\Support\SecretCheck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $loginId = $this->input('login_id');

        if (is_string($loginId)) {
            $this->merge(['login_id' => Str::lower($loginId)]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    /**
     * ログインID とパスワードで認証する。失敗の表示は、ログインID が無いときとパスワードが違うときで同じにする。
     * かかる時間も同じにする（ログインID が無いときも照合を1回行い、失敗は決まった時間まで待つ。App\Support\SecretCheck）。
     * Auth::attempt は、ログインID が無いと照合を飛ばし、時間を Timebox（200ミリ秒）だけでそろえる。
     * ハッシュの計算がそれより長いサーバーでは差が出るので、自分で照らしてから Auth::login する
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $limiter = $this->limiter();
        $limiter->ensureNotLimited('login_id', 'auth.throttle');

        $loginId = $this->string('login_id')->toString();
        $password = $this->string('password')->toString();

        $user = SecretCheck::withinTimebox(function () use ($loginId, $password): ?User {
            $user = User::query()->where('login_id', $loginId)->first();

            return SecretCheck::matches($password, $user?->password) ? $user : null;
        });

        if ($user === null) {
            $limiter->hit();

            throw ValidationException::withMessages([
                'login_id' => __('auth.failed'),
            ]);
        }

        // Auth::attempt と同じく、ハッシュの設定が変わっていれば作り直す
        Auth::getProvider()->rehashPasswordIfRequired($user, ['password' => $password]);
        Auth::login($user, $this->boolean('remember'));

        $limiter->clear();
    }

    /**
     * 失敗の数え（ログインID｜IP で5回、IP だけで20回。どちらも1分）
     */
    private function limiter(): FailureLimiter
    {
        return new FailureLimiter('login', $this->string('login_id')->toString(), (string) $this->ip());
    }
}
