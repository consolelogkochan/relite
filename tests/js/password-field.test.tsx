import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import { afterEach, expect, test, vi } from 'vitest';

import { PasswordField } from '@/components/password-field';
import Register from '@/pages/Register';

afterEach(() => {
    cleanup();
});

function renderField(onSubmit = vi.fn()) {
    render(
        <form
            onSubmit={(event) => {
                event.preventDefault();
                onSubmit();
            }}
        >
            <PasswordField id="password" label="パスワード" defaultValue="secret-pass" />
        </form>,
    );

    return {
        input: screen.getByLabelText<HTMLInputElement>('パスワード'),
        onSubmit,
    };
}

test('初めは隠れている', () => {
    const { input } = renderField();

    expect(input.type).toBe('password');
});

test('目のボタンを押すと見え、もう一度押すと隠れる', () => {
    const { input } = renderField();

    fireEvent.click(screen.getByRole('button', { name: 'パスワードを表示' }));
    expect(input.type).toBe('text');

    fireEvent.click(screen.getByRole('button', { name: 'パスワードを隠す' }));
    expect(input.type).toBe('password');
});

test('目の絵は今の状態を表す（隠れているときは閉じた目、見えているときは開いた目）', () => {
    renderField();
    const button = screen.getByRole('button');
    const icon = () => button.querySelector('svg')?.getAttribute('data-icon');

    expect(icon()).toBe('eye-off');
    fireEvent.click(button);
    expect(icon()).toBe('eye');
    fireEvent.click(button);
    expect(icon()).toBe('eye-off');
});

test('目のボタンを押しても、フォームは送られない', () => {
    const { onSubmit } = renderField();

    fireEvent.click(screen.getByRole('button', { name: 'パスワードを表示' }));
    fireEvent.click(screen.getByRole('button', { name: 'パスワードを隠す' }));

    expect(onSubmit).not.toHaveBeenCalled();
});

test('ボタンの読み上げの名前が、状態に合わせて変わる', () => {
    renderField();
    const button = screen.getByRole('button');

    expect(button.getAttribute('aria-label')).toBe('パスワードを表示');
    fireEvent.click(button);
    expect(button.getAttribute('aria-label')).toBe('パスワードを隠す');
    fireEvent.click(button);
    expect(button.getAttribute('aria-label')).toBe('パスワードを表示');
});

test('登録の2つのパスワードの欄は、それぞれ別に切り替わる', () => {
    render(<Register />);
    const password = screen.getByLabelText<HTMLInputElement>('パスワード（8文字以上）');
    const confirmation = screen.getByLabelText<HTMLInputElement>('パスワード（確認）');
    const [passwordButton, confirmationButton] = screen.getAllByRole('button', {
        name: 'パスワードを表示',
    });

    expect(password.type).toBe('password');
    expect(confirmation.type).toBe('password');

    fireEvent.click(passwordButton);
    expect(password.type).toBe('text');
    expect(confirmation.type).toBe('password');

    fireEvent.click(confirmationButton);
    expect(password.type).toBe('text');
    expect(confirmation.type).toBe('text');

    fireEvent.click(passwordButton);
    expect(password.type).toBe('password');
    expect(confirmation.type).toBe('text');
});
