import { Link, usePage } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import type { SharedProps } from '@/types';

export default function Dashboard() {
    const { auth } = usePage<SharedProps>().props;

    return (
        <main className="mx-auto flex min-h-screen max-w-3xl flex-col gap-6 px-4 py-8">
            <header className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold">ReLite</h1>
                <div className="flex items-center gap-3">
                    <span className="text-sm">{auth.user?.name}</span>
                    <Button asChild variant="outline">
                        <Link href="/logout" method="post" as="button">
                            ログアウト
                        </Link>
                    </Button>
                </div>
            </header>
        </main>
    );
}
