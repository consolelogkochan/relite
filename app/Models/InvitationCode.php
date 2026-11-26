<?php

namespace App\Models;

use App\Support\RandomCode;
use Database\Factories\InvitationCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code'])]
class InvitationCode extends Model
{
    /** @use HasFactory<InvitationCodeFactory> */
    use HasFactory;

    /** コードの長さ */
    public const CODE_LENGTH = 12;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    /**
     * 使った利用者
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 使用済みか。used_at だけで決める
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * 小文字の英字と数字で、まだ使われていないコードを1つ作る
     */
    public static function newUniqueCode(): string
    {
        return RandomCode::unique(
            self::CODE_LENGTH,
            fn (string $candidate): bool => static::query()->where('code', $candidate)->exists(),
            '招待コード',
        );
    }
}
