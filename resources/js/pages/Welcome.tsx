import { Link, usePage } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import type { SharedProps } from '@/types';

export default function Welcome() {
    const { auth } = usePage<SharedProps>().props;

    return (
        <main className="flex min-h-screen flex-col items-center justify-center gap-4">
            <h1 className="text-3xl font-semibold">ReLite</h1>
            {auth.user !== null ? (
                <Button asChild>
                    <Link href="/dashboard">ボードへ</Link>
                </Button>
            ) : (
                <div className="flex gap-2">
                    <Button asChild>
                        <Link href="/login">ログイン</Link>
                    </Button>
                    <Button asChild variant="outline">
                        <Link href="/register">登録</Link>
                    </Button>
                </div>
            )}
        </main>
    );
}
