import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { Field } from '@/components/field';
import { Button } from '@/components/ui/button';
import { submitOnEnter } from '@/lib/ime';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        invitation_code: '',
        login_id: '',
        name: '',
        password: '',
        password_confirmation: '',
    });

    const submit = () => {
        post('/register', {
            onFinish: () =>
                setData((previous) => ({ ...previous, password: '', password_confirmation: '' })),
        });
    };

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        submit();
    };

    return (
        <main className="mx-auto flex min-h-screen max-w-sm flex-col justify-center gap-6 px-4">
            <h1 className="text-2xl font-semibold">登録</h1>
            <form
                onSubmit={onSubmit}
                onKeyDown={(event) => submitOnEnter(event, submit)}
                className="flex flex-col gap-4"
            >
                <Field
                    id="invitation_code"
                    label="招待コード"
                    autoComplete="off"
                    value={data.invitation_code}
                    onChange={(event) => setData('invitation_code', event.target.value)}
                    error={errors.invitation_code}
                />
                <Field
                    id="login_id"
                    label="ログインID（半角の英小文字・数字・_・-、3〜30文字）"
                    autoComplete="username"
                    value={data.login_id}
                    onChange={(event) => setData('login_id', event.target.value)}
                    error={errors.login_id}
                />
                <Field
                    id="name"
                    label="表示の名前（50文字まで）"
                    autoComplete="nickname"
                    value={data.name}
                    onChange={(event) => setData('name', event.target.value)}
                    error={errors.name}
                />
                <Field
                    id="password"
                    label="パスワード（8文字以上）"
                    type="password"
                    autoComplete="new-password"
                    value={data.password}
                    onChange={(event) => setData('password', event.target.value)}
                    error={errors.password}
                />
                <Field
                    id="password_confirmation"
                    label="パスワード（確認）"
                    type="password"
                    autoComplete="new-password"
                    value={data.password_confirmation}
                    onChange={(event) => setData('password_confirmation', event.target.value)}
                    error={errors.password_confirmation}
                />
                <Button type="submit" disabled={processing}>
                    登録
                </Button>
            </form>
            <p className="text-sm">
                アカウントがある人は{' '}
                <Link href="/login" className="underline">
                    ログイン
                </Link>
            </p>
        </main>
    );
}
