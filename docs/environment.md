# 環境と構成

- **用途**：環境と、コードの構成の今の状態の一覧。変わったら書き換える（経緯は `docs/decisions.md` に書く）。育って大きくなったら、コードの構成を別のファイルに分ける
- **書かないもの**：アカウントや鍵の守り方、サーバーの IP アドレスやアカウントの ID などの伏せる値、個人の名前。このファイルは公開される

## 環境

- 公開する先：ロリポップのスタンダードプラン（2026-11-30 の更新から。手続きは人が行う。済んだかは未確認）
- PHP：選べる版は 8.3・8.4・8.5（サーバーによって異なり、ドメインごとに選ぶ）
- データベース：MySQL 8.4
- SSH：使える（スタンダード以上）。root の権限は無い
- cron：10件まで、最短1分毎
- `node`・`npm`・`composer`：SSH で使えるコマンドの公式の一覧に無い（`git` と `php` はある）
- 出典と読んだ日は `docs/decisions.md` の「2-1 サーバーの選定」の項
- 使う技術（決めた。コードはまだ無い）：Laravel 13、Inertia（v3）、React 19、TypeScript、Tailwind CSS 4、shadcn/ui。本番のデータベースは MySQL 8.4。外の AI の入口のログインは Sanctum のトークン
- 使う技術の出典と理由は `docs/decisions.md` の「2-2 技術の選定」の項

## コードの構成

中身の無いアプリ（2026-11-23、2-3）。Laravel 13 の空のアプリに、Inertia・React・TypeScript・Tailwind CSS・shadcn/ui を足した（スターターキットは使っていない）。

- 画面のページ：`resources/js/pages/`（Inertia の画面。今は `Welcome.tsx` だけ）。入口は `resources/js/app.tsx`、外枠は `resources/views/app.blade.php`
- 画面の部品：`resources/js/components/ui/`（shadcn/ui の部品。今はボタンだけ）、`resources/js/lib/`
- 画面への入口：`routes/web.php`
- テスト：サーバーの側は `tests/Feature/`（PHPUnit）、画面の側は `tests/js/`（Vitest と React Testing Library）
- 手元で動かす環境：`compose.yaml`（Sail。PHP 8.5 と MySQL 8.4）。テストは MySQL の `testing` のデータベースで動く（`phpunit.xml`）
- 設定のファイル：`vite.config.ts`（ビルド）、`vitest.config.ts`（画面の側のテスト）、`tsconfig.json`（型）、`eslint.config.js`・`.prettierrc`（書き方）、`phpstan.neon`（Larastan）、`components.json`（shadcn/ui）
- 手元の開発でも、Inertia のサーバーの側で画面を描く機能（SSR）は止めている（`.env` の `INERTIA_SSR_ENABLED=false`）。本番で使わないため
