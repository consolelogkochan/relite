<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;

/**
 * ログインID とひみつの値（パスワード・回復コード）を照らすときに、
 * 「ログインID が無い」ことが、かかる時間から分からないようにする。
 *
 * 2つを重ねている。
 * - ログインID が無いときも、見本のハッシュで照合を1回行う（ハッシュの計算の時間をそろえる）
 * - 失敗したときは、決まった時間（MIN_MICROSECONDS）まで待ってから返す（Laravel の Auth::attempt と同じ Timebox）
 */
final class SecretCheck
{
    /** 失敗のときに、少なくともこれだけの時間をかける（マイクロ秒。Laravel の Auth::attempt の既定と同じ） */
    public const MIN_MICROSECONDS = 200000;

    /** ログインID が無いときに照らす、見本のハッシュ（プロセスごとに1回だけ作る） */
    private static ?string $dummyHash = null;

    /**
     * そのままの値がハッシュと合うか。ハッシュが無い（ログインID が無い・回復コードが無い）ときも、
     * 見本のハッシュで照合を行ってから false を返す
     */
    public static function matches(string $plain, ?string $hashed): bool
    {
        if ($hashed === null || $hashed === '') {
            Hash::check($plain, self::dummyHash());

            return false;
        }

        return Hash::check($plain, $hashed);
    }

    /**
     * 照らす処理を Timebox の中で行う。結果が null（失敗）なら決まった時間まで待ち、
     * null でなければ（成功）待たずに返す
     *
     * @template T of object
     *
     * @param  Closure(): (T|null)  $check
     * @return T|null
     */
    public static function withinTimebox(Closure $check): ?object
    {
        return (new Timebox)->call(function (Timebox $timebox) use ($check): ?object {
            $result = $check();

            if ($result !== null) {
                $timebox->returnEarly();
            }

            return $result;
        }, self::MIN_MICROSECONDS);
    }

    private static function dummyHash(): string
    {
        return self::$dummyHash ??= Hash::make(Str::random(32));
    }
}
