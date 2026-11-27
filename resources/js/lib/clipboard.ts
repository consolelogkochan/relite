/**
 * 文字を写す（クリップボードに入れる）。写せたら true。
 * ブラウザが許さないとき（安全でない接続など）は false を返し、例外を外に出さない
 */
export async function copyText(text: string): Promise<boolean> {
    if (typeof navigator === 'undefined' || navigator.clipboard === undefined) {
        return false;
    }

    try {
        await navigator.clipboard.writeText(text);

        return true;
    } catch {
        return false;
    }
}
