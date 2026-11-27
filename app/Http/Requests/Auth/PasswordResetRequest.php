<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\FailureLimiter;
use App\Support\RecoveryCode;
use App\Support\SecretCheck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * 回復コードでパスワードを再設定する要求（ログインID・回復コード・新しいパスワード2回を一度に受ける）
 */
class PasswordResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * ログインID は小文字に、回復コードはハイフンを取って小文字にそろえる（App\Support\RecoveryCode）
     */
    protected function prepareForValidation(): void
    {
        $loginId = $this->input('login_id');
        $code = $this->input('recovery_code');

        $this->merge(array_filter([
            'login_id' => is_string($loginId) ? Str::lower($loginId) : null,
            'recovery_code' => is_string($code) ? RecoveryCode::normalize($code) : null,
        ], fn ($value) => $value !== null));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'login_id' => ['required', 'string'],
            'recovery_code' => ['required', 'string', 'max:64'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * ログインID と回復コードを照らし、合えば利用者を返す。失敗の表示は、ログインID が無いときと
     * 回復コードが違うときで同じにし、かかる時間も同じにする（App\Support\SecretCheck）
     *
     * @throws ValidationException
     */
    public function authenticatedUser(): User
    {
        $limiter = $this->limiter();
        $limiter->ensureNotLimited('login_id', 'auth.reset_throttle');

        $loginId = $this->string('login_id')->toString();
        $code = $this->string('recovery_code')->toString();

        $user = SecretCheck::withinTimebox(function () use ($loginId, $code): ?User {
            $user = User::query()->where('login_id', $loginId)->first();

            return SecretCheck::matches($code, $user?->recovery_code_hash) ? $user : null;
        });

        if ($user === null) {
            $limiter->hit();

            throw ValidationException::withMessages([
                'login_id' => __('auth.reset_failed'),
            ]);
        }

        $limiter->clear();

        return $user;
    }

    /**
     * 失敗の数え（ログインと同じ仕組み。数えは分ける）
     */
    private function limiter(): FailureLimiter
    {
        return new FailureLimiter('password-reset', $this->string('login_id')->toString(), (string) $this->ip());
    }
}
