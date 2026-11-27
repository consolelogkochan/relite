<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * ログインと再設定の失敗を数える。失敗だけを数え、2つを重ねる。
 * - 「ログインID｜IP」の組で PER_LOGIN_ID 回まで（1つのログインID を狙う総当たりを止める）
 * - 「IP だけ」で PER_IP 回まで（1つの IP からログインID を変えながら試す攻撃を止める）
 * どちらも DECAY_SECONDS で戻る。成功したら「ログインID｜IP」だけを消す
 * （「IP だけ」を成功で消すと、自分のアカウントでの成功を挟んで数えを戻せるため）
 */
final class FailureLimiter
{
    public const PER_LOGIN_ID = 5;

    public const PER_IP = 20;

    public const DECAY_SECONDS = 60;

    /**
     * @param  string  $name  数えを分ける名前（login・password-reset）
     */
    public function __construct(
        private readonly string $name,
        private readonly string $loginId,
        private readonly string $ip,
    ) {}

    /**
     * どちらかの上限を超えていれば、待つ秒数を入れた誤りを投げる
     *
     * @param  string  $field  誤りを付ける欄
     * @param  string  $messageKey  誤りの文の翻訳のキー（:seconds を持つ）
     *
     * @throws ValidationException
     */
    public function ensureNotLimited(string $field, string $messageKey): void
    {
        $seconds = 0;

        foreach ($this->limits() as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                $seconds = max($seconds, RateLimiter::availableIn($key));
            }
        }

        if ($seconds > 0) {
            throw ValidationException::withMessages([
                $field => __($messageKey, ['seconds' => $seconds]),
            ]);
        }
    }

    /**
     * 失敗を1回数える（両方のキー）
     */
    public function hit(): void
    {
        foreach (array_keys($this->limits()) as $key) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
        }
    }

    /**
     * 成功したとき、「ログインID｜IP」の数えだけを消す
     */
    public function clear(): void
    {
        RateLimiter::clear($this->loginIdKey());
    }

    /**
     * @return array<string, int>
     */
    private function limits(): array
    {
        return [
            $this->loginIdKey() => self::PER_LOGIN_ID,
            $this->name.':ip:'.$this->ip => self::PER_IP,
        ];
    }

    private function loginIdKey(): string
    {
        return $this->name.':login_id:'.$this->loginId.'|'.$this->ip;
    }
}
