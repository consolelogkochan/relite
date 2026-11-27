import { cleanup, render, screen } from '@testing-library/react';
import { afterEach, expect, test, vi } from 'vitest';

import RecoveryCode from '@/pages/RecoveryCode';

const page = vi.hoisted(() => ({ flash: {} as Record<string, unknown> }));

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ flash: page.flash }),
    Link: ({ href, children }: { href: string; children: React.ReactNode }) => (
        <a href={href}>{children}</a>
    ),
}));

afterEach(() => {
    cleanup();
    page.flash = {};
});

const next = { href: '/dashboard', label: '次へ' };

test('flash に回復コードがあれば、区切った形と注意の文と写しのボタンを出す', () => {
    page.flash = { recoveryCode: 'abcd-efgh-ijkl-mnop' };
    render(<RecoveryCode next={next} />);

    expect(screen.getByLabelText('回復コード').textContent).toBe('abcd-efgh-ijkl-mnop');
    expect(screen.getByText('この画面を離れると二度と見られません。')).toBeTruthy();
    expect(screen.getByRole('button', { name: '写す' })).toBeTruthy();
    expect(screen.getByRole('link', { name: '次へ' }).getAttribute('href')).toBe('/dashboard');
});

test('flash に回復コードが無ければ（読み込み直したとき）、もう見られないと出す', () => {
    render(<RecoveryCode next={next} />);

    expect(screen.queryByLabelText('回復コード')).toBeNull();
    expect(screen.getByText(/回復コードは、もう見られません/)).toBeTruthy();
    expect(screen.getByRole('link', { name: '次へ' })).toBeTruthy();
});
