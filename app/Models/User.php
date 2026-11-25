<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use RuntimeException;

#[Fillable(['name', 'login_id', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** 共有ID の長さ */
    public const SHARE_ID_LENGTH = 12;

    /** 共有ID に使う文字（小文字の英字と数字） */
    private const SHARE_ID_ALPHABET = 'abcdefghijklmnopqrstuvwxyz0123456789';

    /** 共有ID の重なりで作り直す回数の上限 */
    private const SHARE_ID_MAX_ATTEMPTS = 10;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'guest_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // 利用者を作るとき、共有ID が空なら作って入れる。
        // 作る前はまだ値が無いことがあるので、属性の生の値（型は mixed）で見る
        static::creating(function (User $user): void {
            if (blank($user->getAttribute('share_id'))) {
                $user->share_id = static::newUniqueShareId();
            }
        });
    }

    /**
     * ゲストかどうか。ゲストの期限が入っていればゲスト
     */
    public function isGuest(): bool
    {
        return $this->guest_expires_at !== null;
    }

    /**
     * 共有ID を作り直して保存する。前の値は使えなくなる
     */
    public function regenerateShareId(): void
    {
        $this->share_id = static::newUniqueShareId();
        $this->save();
    }

    /**
     * 小文字の英字と数字で、まだ使われていない共有ID を作る。
     * 重なったら作り直し、上限の回数を超えたら例外にする
     */
    public static function newUniqueShareId(): string
    {
        for ($attempt = 0; $attempt < self::SHARE_ID_MAX_ATTEMPTS; $attempt++) {
            $candidate = '';
            for ($i = 0; $i < self::SHARE_ID_LENGTH; $i++) {
                $candidate .= self::SHARE_ID_ALPHABET[random_int(0, strlen(self::SHARE_ID_ALPHABET) - 1)];
            }

            if (! static::query()->where('share_id', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new RuntimeException('共有ID を作れませんでした（重なりが続いた）。');
    }
}
