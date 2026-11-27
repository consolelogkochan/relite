import { cleanup, render, screen } from '@testing-library/react';
import type { ReactElement } from 'react';
import { afterEach, expect, test } from 'vitest';

import Login from '@/pages/Login';
import PasswordReset from '@/pages/PasswordReset';
import Register from '@/pages/Register';

afterEach(() => {
    cleanup();
});

test.each<[string, ReactElement]>([
    ['ログイン', <Login status={null} />],
    ['登録', <Register />],
    ['パスワードの再設定', <PasswordReset />],
])('%sの画面に、玄関へ戻る入口がある', (_name, page) => {
    render(page);

    expect(screen.getByRole('link', { name: 'トップへ戻る' }).getAttribute('href')).toBe('/');
});
