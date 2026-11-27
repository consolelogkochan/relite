import { router } from '@inertiajs/react';
import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import { afterEach, expect, test, vi } from 'vitest';

import Login from '@/pages/Login';
import PasswordReset from '@/pages/PasswordReset';

afterEach(() => {
    cleanup();
    vi.restoreAllMocks();
});

test('ログインの画面に「パスワードを忘れたとき」の入口がある', () => {
    render(<Login status={null} />);

    expect(screen.getByRole('link', { name: 'パスワードを忘れたとき' }).getAttribute('href')).toBe(
        '/password/reset',
    );
});

test('ログインの画面に、再設定の後の知らせが出る', () => {
    render(<Login status="新しいパスワードでログインしてください。" />);

    expect(screen.getByText('新しいパスワードでログインしてください。')).toBeTruthy();
});

test('再設定の画面に、ログインの画面へ戻る入口がある', () => {
    render(<PasswordReset />);

    expect(screen.getByRole('link', { name: 'ログインの画面へ戻る' }).getAttribute('href')).toBe(
        '/login',
    );
});

test('再設定の画面のパスワードの2つの欄は、目のボタンつきの欄（PasswordField）', () => {
    render(<PasswordReset />);

    expect(screen.getByLabelText<HTMLInputElement>('新しいパスワード（8文字以上）').type).toBe(
        'password',
    );
    expect(screen.getByLabelText<HTMLInputElement>('新しいパスワード（確認）').type).toBe(
        'password',
    );
    expect(screen.getAllByRole('button', { name: 'パスワードを表示' })).toHaveLength(2);
});

test('再設定の画面で、変換中の Enter では送らず、ふつうの Enter では送る', () => {
    const visit = vi.spyOn(router, 'visit').mockImplementation(() => {});
    render(<PasswordReset />);
    const recoveryCode = screen.getByLabelText('回復コード（ハイフンは入れても入れなくてもよい）');

    fireEvent.keyDown(recoveryCode, { key: 'Enter', isComposing: true });
    expect(visit).not.toHaveBeenCalled();

    fireEvent.keyDown(recoveryCode, { key: 'Enter' });
    expect(visit).toHaveBeenCalledTimes(1);
    expect(visit.mock.calls[0]?.[0]).toBe('/password/reset');
});
