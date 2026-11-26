import { Eye, EyeOff } from 'lucide-react';
import type { InputHTMLAttributes } from 'react';

import { usePasswordVisibility } from '@/lib/use-password-visibility';

type PasswordFieldProps = Omit<InputHTMLAttributes<HTMLInputElement>, 'type'> & {
    id: string;
    label: string;
    error?: string;
};

/**
 * 目のボタンで見せるか隠すかを切り替えられるパスワードの欄（ラベル・入力欄・誤りの文の形は Field と同じ）。
 * ボタンは type="button" なので、押してもフォームは送られない
 */
export function PasswordField({ id, label, error, ...input }: PasswordFieldProps) {
    const { visible, toggle } = usePasswordVisibility();

    return (
        <div className="flex flex-col gap-1">
            <label htmlFor={id} className="text-sm font-medium">
                {label}
            </label>
            <div className="relative">
                <input
                    id={id}
                    name={id}
                    type={visible ? 'text' : 'password'}
                    className="w-full rounded-md border px-3 py-2 pr-10"
                    aria-invalid={error !== undefined}
                    {...input}
                />
                <button
                    type="button"
                    onClick={toggle}
                    aria-label={visible ? 'パスワードを隠す' : 'パスワードを表示'}
                    aria-controls={id}
                    className="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"
                >
                    {visible ? (
                        <EyeOff aria-hidden="true" className="size-4" />
                    ) : (
                        <Eye aria-hidden="true" className="size-4" />
                    )}
                </button>
            </div>
            {error !== undefined && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}
