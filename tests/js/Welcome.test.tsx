import { render, screen } from '@testing-library/react';
import { expect, test, vi } from 'vitest';

import Welcome from '@/pages/Welcome';

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ props: { auth: { user: null } } }),
    Link: ({ href, children }: { href: string; children: React.ReactNode }) => (
        <a href={href}>{children}</a>
    ),
}));

test('Welcome に ReLite の名前が出る', () => {
    render(<Welcome />);

    expect(screen.getByRole('heading', { name: 'ReLite' })).toBeTruthy();
});
