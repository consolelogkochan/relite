import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { Field } from '@/components/field';
import { PasswordField } from '@/components/password-field';
import { Button } from '@/components/ui/button';
import { submitOnEnter } from '@/lib/ime';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        login_id: '',
        password: '',
        remember: false,
    });

    const submit = () => {
        post('/login', { onFinish: () => setData('password', '') });
    };

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        submit();
    };

    return (
        <main className="mx-auto flex min-h-screen max-w-sm flex-col justify-center gap-6 px-4">
            <h1 className="text-2xl font-semibold">ログイン</h1>
            <form
                onSubmit={onSubmit}
                onKeyDown={(event) => submitOnEnter(event, submit)}
                className="flex flex-col gap-4"
            >
                <Field
                    id="login_id"
                    label="ログインID"
                    autoComplete="username"
                    value={data.login_id}
                    onChange={(event) => setData('login_id', event.target.value)}
                    error={errors.login_id}
                />
                <PasswordField
                    id="password"
                    label="パスワード"
                    autoComplete="current-password"
                    value={data.password}
                    onChange={(event) => setData('password', event.target.value)}
                    error={errors.password}
                />
                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="remember"
                        checked={data.remember}
                        onChange={(event) => setData('remember', event.target.checked)}
                    />
                    ログインしたままにする
                </label>
                <Button type="submit" disabled={processing}>
                    ログイン
                </Button>
            </form>
            <p className="text-sm">
                アカウントが無い人は{' '}
                <Link href="/register" className="underline">
                    登録
                </Link>
            </p>
            <p className="text-sm">
                <Link href="/" className="underline">
                    トップへ戻る
                </Link>
            </p>
        </main>
    );
}
