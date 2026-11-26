import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import { afterEach, expect, test, vi } from 'vitest';

import { submitOnEnter } from '@/lib/ime';

afterEach(() => {
    cleanup();
});

function renderForm(submit: () => void) {
    render(
        <form onKeyDown={(event) => submitOnEnter(event, submit)}>
            <input aria-label="入力" />
        </form>,
    );

    return screen.getByLabelText('入力');
}

test('変換中の Enter では送信の処理を呼ばない', () => {
    const submit = vi.fn();
    const input = renderForm(submit);

    fireEvent.keyDown(input, { key: 'Enter', isComposing: true });

    expect(submit).not.toHaveBeenCalled();
});

test('keyCode 229 の Enter（変換中）では送信の処理を呼ばない', () => {
    const submit = vi.fn();
    const input = renderForm(submit);

    fireEvent.keyDown(input, { key: 'Enter', keyCode: 229 });

    expect(submit).not.toHaveBeenCalled();
});

test('ふつうの Enter では送信の処理を呼ぶ', () => {
    const submit = vi.fn();
    const input = renderForm(submit);

    fireEvent.keyDown(input, { key: 'Enter' });

    expect(submit).toHaveBeenCalledTimes(1);
});
