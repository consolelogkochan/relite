import type { KeyboardEvent } from 'react';

/**
 * 入力欄の Enter を扱う（画面全体の決まり：日本語の変換を確定する Enter で、登録や送信をしない）。
 * フォームの onKeyDown に付ける。入力欄（input）の Enter は、ブラウザに送信させず、
 * 変換中でなければ submit を呼ぶ。textarea・ボタンなどの Enter は変えない。
 */
export function submitOnEnter(event: KeyboardEvent<HTMLFormElement>, submit: () => void): void {
    if (event.key !== 'Enter' || !(event.target instanceof HTMLInputElement)) {
        return;
    }

    event.preventDefault();

    // 変換中の Enter。isComposing を持たないブラウザのために、keyCode 229 も見る
    if (event.nativeEvent.isComposing || event.keyCode === 229) {
        return;
    }

    submit();
}
