import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { Field } from '@/components/field';
import { PasswordField } from '@/components/password-field';
import { Button } from '@/components/ui/button';
import { submitOnEnter } from '@/lib/ime';

/** 回復コードでパスワードを再設定する画面 */
export default function PasswordReset() {
    const { data, setData, post, processing, errors } = useForm({
        login_id: '',
        recovery_code: '',
        password: '',
        password_confirmation: '',
    });

    const submit = () => {
        post('/password/reset', {
            onFinish: () =>
                setData((previous) => ({
                    ...previous,
                    recovery_code: '',
                    password: '',
                    password_confirmation: '',
                })),
        });
    };

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        submit();
    };

    return (
        <main className="mx-auto flex min-h-screen max-w-sm flex-col justify-center gap-6 px-4">
            <h1 className="text-2xl font-semibold">パスワードの再設定</h1>
            <p className="text-sm">登録のときに見せた回復コードで、新しいパスワードを決めます。</p>
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
                <Field
                    id="recovery_code"
                    label="回復コード（ハイフンは入れても入れなくてもよい）"
                    autoComplete="off"
                    value={data.recovery_code}
                    onChange={(event) => setData('recovery_code', event.target.value)}
                    error={errors.recovery_code}
                />
                <PasswordField
                    id="password"
                    label="新しいパスワード（8文字以上）"
                    autoComplete="new-password"
                    value={data.password}
                    onChange={(event) => setData('password', event.target.value)}
                    error={errors.password}
                />
                <PasswordField
                    id="password_confirmation"
                    label="新しいパスワード（確認）"
                    autoComplete="new-password"
                    value={data.password_confirmation}
                    onChange={(event) => setData('password_confirmation', event.target.value)}
                    error={errors.password_confirmation}
                />
                <Button type="submit" disabled={processing}>
                    再設定する
                </Button>
            </form>
            <p className="text-sm">
                <Link href="/login" className="underline">
                    ログインの画面へ戻る
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
