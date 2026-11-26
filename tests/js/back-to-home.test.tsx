import { cleanup, render, screen } from '@testing-library/react';
import { afterEach, expect, test } from 'vitest';

import Login from '@/pages/Login';
import Register from '@/pages/Register';

afterEach(() => {
    cleanup();
});

test.each([
    ['ログイン', Login],
    ['登録', Register],
])('%sの画面に、玄関へ戻る入口がある', (_name, Page) => {
    render(<Page />);

    expect(screen.getByRole('link', { name: 'トップへ戻る' }).getAttribute('href')).toBe('/');
});
