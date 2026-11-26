import type { InputHTMLAttributes } from 'react';

type FieldProps = InputHTMLAttributes<HTMLInputElement> & {
    id: string;
    label: string;
    error?: string;
};

/** ラベルと入力欄と誤りの文をまとめた欄 */
export function Field({ id, label, error, ...input }: FieldProps) {
    return (
        <div className="flex flex-col gap-1">
            <label htmlFor={id} className="text-sm font-medium">
                {label}
            </label>
            <input
                id={id}
                name={id}
                className="rounded-md border px-3 py-2"
                aria-invalid={error !== undefined}
                {...input}
            />
            {error !== undefined && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}
