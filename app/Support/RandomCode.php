<?php

namespace App\Support;

use Closure;
use RuntimeException;

/**
 * 小文字の英字と数字で、まだ使われていない値を作る（共有ID・招待コード）
 */
final class RandomCode
{
    /** 使う文字（小文字の英字と数字） */
    public const ALPHABET = 'abcdefghijklmnopqrstuvwxyz0123456789';

    /** 重なりで作り直す回数の上限 */
    public const MAX_ATTEMPTS = 10;

    /**
     * 決まった長さの値を1つ作る。文字は一覧から random_int で1文字ずつ選ぶ
     */
    public static function make(int $length): string
    {
        $value = '';
        for ($i = 0; $i < $length; $i++) {
            $value .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return $value;
    }

    /**
     * まだ使われていない値を作る。$exists が true を返す値は作り直し、上限を超えたら例外にする
     *
     * @param  Closure(string): bool  $exists
     */
    public static function unique(int $length, Closure $exists, string $what): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $candidate = self::make($length);

            if (! $exists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException("{$what}を作れませんでした（重なりが続いた）。");
    }
}
