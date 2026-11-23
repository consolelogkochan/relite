import { render, screen } from '@testing-library/react';
import { expect, test } from 'vitest';

import Welcome from '@/pages/Welcome';

test('Welcome に ReLite の名前が出る', () => {
    render(<Welcome />);

    expect(screen.getByRole('heading', { name: 'ReLite' })).toBeTruthy();
});
