/** HandleInertiaRequests が全ての画面に渡すデータ */
export type SharedProps = {
    auth: {
        user: { name: string } | null;
    };
};
