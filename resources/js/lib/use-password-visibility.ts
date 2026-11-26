import { useState } from 'react';

/**
 * パスワードを見せるか隠すかの状態。初めは隠す。
 * 欄ごとに呼ぶ（1つの欄に1つの状態。登録のパスワードと確認の欄は、別々に切り替わる）
 */
export function usePasswordVisibility(): { visible: boolean; toggle: () => void } {
    const [visible, setVisible] = useState(false);

    return { visible, toggle: () => setVisible((current) => !current) };
}
