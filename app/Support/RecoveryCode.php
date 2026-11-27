<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * 回復コード（パスワードを忘れたときに、本人だけが持つ値で再設定するためのもの）。
 *
 * - 作る：16文字の小文字の英字と数字（RandomCode）
 * - 保存する：bcrypt のハッシュだけ（users の recovery_code_hash）。そのままの値はどこにも保存しない
 * - 見せる：4文字ずつハイフンで区切る（例：abcd-efgh-ijkl-mnop の形）
 * - 照らす：入力のハイフンを取り、小文字にそろえてから照らす。
 *   見せるときにこちらが区切っているので、区切りを入れたまま打つ人や、大文字で書き写す人が出るのは、
 *   こちらの見せ方から起きる。その違いで断ると、正しい回復コードを持つ本人が締め出されるため
 *   （入力した人の責任とし、そのまま照らす設計もありうる）
 */
final class RecoveryCode
{
    /** 回復コードの長さ（区切りを除く） */
    public const LENGTH = 16;

    /** 見せるときの区切りの間隔 */
    private const GROUP = 4;

    /**
     * 新しい回復コードを作る（区切りの無い形）
     */
    public static function generate(): string
    {
        return RandomCode::make(self::LENGTH);
    }

    /**
     * 見せる形にする（4文字ずつハイフンで区切る）
     */
    public static function format(string $code): string
    {
        return implode('-', str_split($code, self::GROUP));
    }

    /**
     * 入力をそろえる（ハイフンを取り、小文字にする）
     */
    public static function normalize(string $input): string
    {
        return Str::lower(str_replace('-', '', $input));
    }

    /**
     * 保存する形（ハッシュ）にする
     */
    public static function hash(string $code): string
    {
        return Hash::make($code);
    }
}
