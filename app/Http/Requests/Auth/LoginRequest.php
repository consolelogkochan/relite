<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /** 失敗を数える上限（これを超えると、待つまで断る） */
    public const MAX_ATTEMPTS = 5;

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
     * ログインID とパスワードで認証する。失敗の表示は、ログインID が無いときとパスワードが違うときで同じにする
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            'login_id' => $this->string('login_id')->toString(),
            'password' => $this->string('password')->toString(),
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login_id' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        throw ValidationException::withMessages([
            'login_id' => __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($this->throttleKey()),
            ]),
        ]);
    }

    /**
     * 失敗を数えるキー（ログインID｜IP）
     */
    private function throttleKey(): string
    {
        return $this->string('login_id')->toString().'|'.$this->ip();
    }
}
