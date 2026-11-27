import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { copyText } from '@/lib/clipboard';

type RecoveryCodeProps = {
    next: { href: string; label: string };
};

/**
 * 回復コードを1回だけ見せる画面。値は前の要求の flash（recoveryCode）で届き、
 * 読み込み直すと消える（サーバーにも、そのままの値は残っていない）
 */
export default function RecoveryCode({ next }: RecoveryCodeProps) {
    const { flash } = usePage();
    const code = typeof flash.recoveryCode === 'string' ? flash.recoveryCode : null;
    const [copied, setCopied] = useState<boolean | null>(null);

    const copy = async () => {
        if (code !== null) {
            setCopied(await copyText(code));
        }
    };

    return (
        <main className="mx-auto flex min-h-screen max-w-sm flex-col justify-center gap-6 px-4">
            <h1 className="text-2xl font-semibold">回復コード</h1>
            {code !== null ? (
                <>
                    <p className="text-sm">
                        パスワードを忘れたときに、この回復コードで再設定します。安全な場所に控えてください。
                    </p>
                    <p
                        className="rounded-md border px-3 py-2 text-center font-mono text-lg"
                        aria-label="回復コード"
                    >
                        {code}
                    </p>
                    <p className="text-sm font-medium text-red-600">
                        この画面を離れると二度と見られません。
                    </p>
                    <Button type="button" variant="outline" onClick={() => void copy()}>
                        写す
                    </Button>
                    {copied === true && <p className="text-sm">写しました。</p>}
                    {copied === false && (
                        <p className="text-sm text-red-600">
                            写せませんでした。手で控えてください。
                        </p>
                    )}
                </>
            ) : (
                <p className="text-sm">
                    回復コードは、もう見られません（作ったときに1回だけ見せています）。
                </p>
            )}
            <Button asChild>
                <Link href={next.href}>{next.label}</Link>
            </Button>
        </main>
    );
}
