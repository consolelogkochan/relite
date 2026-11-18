# 構想

- **用途**：ReLite の構想。誰に向けて、なぜ、何を作るか。機能の一覧は、今のアプリ project-lite の機能のうち何を残し、何をどう磨くかを決める材料にする
- **書かないもの**：アカウントや鍵の守り方、サーバーの IP アドレスやアカウントの ID などの伏せる値、個人の名前、今のアプリの弱点（今も公開しているため）。このファイルは公開される
- 決めた経緯は `docs/decisions.md` の「1-1 構想」の項にある

## 誰に

- 使うのは、知人と自分だけの見込み
- ポートフォリオとしては、不特定多数が使う想定のものにする
- サーバーや環境は小さくてよい

## なぜ

今の project-lite を、次のために作り直す。

- カードのドラッグ＆ドロップ、カレンダー、タイムラインの動作を安定させる
- 通知を見直す。今はメールと連動しているが、連動させない方式にしたい
- メールアドレスの確認を見直す。今は最初の登録でメールアドレスを登録しているが、メールアドレスの登録を不要にしたい
- 共有を見直す。共有する相手を、何で探すかを見直す
- タイムラインを、今の縦軸（日・週・月・時間を基準にした見た目）から、横軸でタスクを基準にした見た目にする
- そのほかの既存の機能の使い勝手を良くする
- 新しい機能として、AI と連携する。アプリの中で AI を使うのではなく、アプリの外から AI でアプリを操作し、タスクの登録や変更をできるようにしたい

### SPA にする理由

今のアプリがページを読み込み直さないのは、ページの読み込みの体験の悪さを、無理に避けたためである。SPA にすれば、カードの登録や変更、ドラッグ＆ドロップによる描き直しと相性が良いと判断した。今の作りの拙さは、設計がうまくできていなかったところが大きい。

## 方針

- 基本は、既存の機能を残しつつ磨く
- メールアドレスの登録は不要にする
- そのほかの取捨選択は、下の機能の一覧で網羅的に判断する
- タイムラインは、有料のライセンスが要らない、無料の部品で作る。そのために機能の取捨選択が要れば行う

## 機能の一覧

今の project-lite（Laravel 12、Blade と Alpine.js）の機能を、利用者の操作の単位で1行ずつ並べた。「今の作り」は、今のリポジトリのファイルの名前で書いた。画面の多くは `resources/views/boards/show.blade.php`（以下 `show.blade.php`）の中にある。「要望との関係」は、上の「なぜ」のどれに関わるか。「扱い」（残す・変える・捨てる・未定）は、人が決める。

| # | まとまり | 機能 | 今の作り | 要望との関係 | 扱い |
| --- | --- | --- | --- | --- | --- |
| 1 | ボード | 自分が属するボードの一覧を見る | `dashboard.blade.php`、`DashboardController` | | 未定 |
| 2 | ボード | ボードを作る（題と背景色） | `board-create-modal.blade.php`、`BoardController` | | 未定 |
| 3 | ボード | ボードを消す（確認の後） | `dashboard.blade.php` のフォーム、`BoardController` | | 未定 |
| 4 | ボード | ボードを開く（リストとカードの画面） | `show.blade.php`、`BoardController` | | 未定 |
| 5 | リスト | リストを足す | `show.blade.php` のフォーム、`ListController` | | 未定 |
| 6 | リスト | リストの題を変える（題を押して編集） | `show.blade.php`、`ListController` | | 未定 |
| 7 | リスト | リストを消す（メニューから） | `show.blade.php`、`ListController` | | 未定 |
| 8 | リスト | リストをドラッグして並べ替える | SortableJS（`resources/js/bootstrap.js` の自作のディレクティブ）、`ListController` | ドラッグ＆ドロップの安定 | 未定 |
| 9 | カード | カードを足す（リストの下のフォーム） | `show.blade.php`、`CardController` | | 未定 |
| 10 | カード | カードをドラッグして、リストの中で並べ替える・リストの間で動かす | SortableJS（`show.blade.php`）、`CardController` | ドラッグ＆ドロップの安定 | 未定 |
| 11 | カード | カードの詳細を開く | `card-detail-modal.blade.php`、`CardController` | | 未定 |
| 12 | カード | カードの題と説明を変える | `card-detail-modal.blade.php`、`CardController` | | 未定 |
| 13 | カード | カードを完了にする（一覧のチェックボックスと詳細から） | `show.blade.php`・`card-detail-modal.blade.php`、`CardController` | | 未定 |
| 14 | カード | 開始日と期限を決める | flatpickr（`card-detail-modal.blade.php`）、`CardController` | | 未定 |
| 15 | カード | リマインダーの日時を決め、その日時に通知を受ける | `card-detail-modal.blade.php`、定期実行のコマンド（`app/Console/Commands/`） | 通知とメールの切り離し | 未定 |
| 16 | カード | カードを消す | `card-detail-modal.blade.php`、`CardController` | | 未定 |
| 17 | カード | 添付した画像を、カードの表紙にする | `card-detail-modal.blade.php`、`CardController` | | 未定 |
| 18 | コメント | コメントを書く・自分のコメントを直す・消す | `card-detail-modal.blade.php`、`CommentController` | | 未定 |
| 19 | ラベル | ボードのラベルを作る・直す・消す（名前と色） | `card-detail-modal.blade.php`、`LabelController` | | 未定 |
| 20 | ラベル | カードにラベルを付ける・外す | `card-detail-modal.blade.php`、`LabelController` | | 未定 |
| 21 | チェックリスト | チェックリストを作る・題を変える・消す | `card-detail-modal.blade.php`、`ChecklistController` | | 未定 |
| 22 | チェックリスト | 項目を足す・直す・完了にする・消す | `card-detail-modal.blade.php`、`ChecklistItemController` | | 未定 |
| 23 | チェックリスト | 項目をドラッグして並べ替える | SortableJS、`ChecklistItemController` | ドラッグ＆ドロップの安定 | 未定 |
| 24 | チェックリスト | 進み具合を見る（詳細では割合の棒、一覧のカードでは完了の数／全体の数） | `card-detail-modal.blade.php`・`show.blade.php` | | 未定 |
| 25 | 添付 | ファイルを添付する・消す | `card-detail-modal.blade.php`、`AttachmentController` | | 未定 |
| 26 | 添付 | 添付の確認の状態を変える（既定は確認待ち） | `card-detail-modal.blade.php`、`AttachmentController` | | 未定 |
| 27 | 添付 | 画像を拡大して見る | `card-detail-modal.blade.php` | | 未定 |
| 28 | メンバーと共有 | 名前かメールアドレスでユーザーを探し、ボードに招待する | `board-invite-modal.blade.php`、`BoardInvitationController` | 共有の相手の探し方 | 未定 |
| 29 | メンバーと共有 | ボードのメンバーの一覧を見る・役割を変える（admin・member・guest） | `board-invite-modal.blade.php`、`BoardInvitationController`、`BoardPolicy` | 共有の相手の探し方 | 未定 |
| 30 | メンバーと共有 | メンバーを外す・自分でボードから抜ける | `board-invite-modal.blade.php`、`BoardInvitationController` | | 未定 |
| 31 | 割り当て | カードにメンバーを割り当てる・外す | `card-detail-modal.blade.php`、割り当て用のコントローラー | | 未定 |
| 32 | 通知 | アプリの中の通知を見る（ヘッダーの一覧、未読の数、未読だけに絞る） | `header.blade.php`、`NotificationController` | 通知とメールの切り離し | 未定 |
| 33 | 通知 | 通知を既読にする・消す・既読をまとめて消す | `header.blade.php`、`NotificationController` | 通知とメールの切り離し | 未定 |
| 34 | 通知 | 通知の種類ごとにオン・オフを決める（コメント・添付・期限・移動・作成・削除の6種類） | `notification-settings-modal.blade.php`、`NotificationController` | 通知とメールの切り離し | 未定 |
| 35 | 通知 | 通知をメールでも送る（アプリの中の通知と同時に、キューで送る） | `app/Notifications/` の各通知、`app/Listeners/` | 通知とメールの切り離し | 未定 |
| 36 | 表示 | Kanban・カレンダー・タイムラインを切り替える | `show.blade.php` | ドラッグ＆ドロップの安定 | 未定 |
| 37 | 表示 | カレンダー（月）で、期限のあるカードを見る | FullCalendar の月表示（`show.blade.php`）、`BoardController` | ドラッグ＆ドロップの安定 | 未定 |
| 38 | 表示 | カレンダーでカードをドラッグして、日付を変える | FullCalendar（`show.blade.php`）、`CardController` | ドラッグ＆ドロップの安定 | 未定 |
| 39 | 表示 | タイムライン（週・日の、縦の時間の軸）でカードを見る | FullCalendar の時間の軸の表示（`show.blade.php`）、`BoardController` | タイムラインの横軸化 | 未定 |
| 40 | 表示 | タイムラインでカードをドラッグ・伸び縮みさせて、日時を変える | FullCalendar（`show.blade.php`）、`CardController` | ドラッグ＆ドロップの安定、タイムラインの横軸化 | 未定 |
| 41 | 表示 | 予定を動かすとき、リマインダーも一緒にずらすかを聞く | `show.blade.php` | | 未定 |
| 42 | 表示 | カードを絞り込む（キーワード・ラベル・期間・メンバー・チェックリスト・完了） | Kanban は `show.blade.php` の中で、カレンダーとタイムラインは `BoardController` で絞り込む | | 未定 |
| 43 | 表示 | ダークモードを切り替える | `header.blade.php` | | 未定 |
| 44 | 表示 | スマートフォンとデスクトップの幅に合わせる | Tailwind CSS | | 未定 |
| 45 | 認証と登録 | 招待コードとメールアドレスで登録する | `auth/register.blade.php`、`RegisteredUserController` | メールアドレスの登録を不要にする | 未定 |
| 46 | 認証と登録 | メールアドレスを確認する（確認のメールのリンク） | Laravel Breeze、`VerifyEmailController` | メールアドレスの登録を不要にする | 未定 |
| 47 | 認証と登録 | メールアドレスとパスワードでログイン・ログアウトする | Laravel Breeze、`AuthenticatedSessionController` | メールアドレスの登録を不要にする | 未定 |
| 48 | 認証と登録 | パスワードを忘れたとき、メールで再設定する | Laravel Breeze、`PasswordResetLinkController`・`NewPasswordController` | メールアドレスの登録を不要にする | 未定 |
| 49 | 認証と登録 | プロフィール（名前・メールアドレス・アバター）を変える | `profile/edit.blade.php`、`ProfileController` | メールアドレスの登録を不要にする | 未定 |
| 50 | 認証と登録 | パスワードを変える | Laravel Breeze、`PasswordController` | | 未定 |
| 51 | 認証と登録 | アカウントを消す | `profile/partials/delete-user-form.blade.php`、`ProfileController` | | 未定 |
| 52 | 管理者 | 招待コードを作り、まだ使われていないコードを見る | `admin/dashboard.blade.php`、`AdminController` | | 未定 |
| 53 | 管理者 | 利用者の一覧を見る（使った招待コードと登録日） | `admin/dashboard.blade.php`、`AdminController` | | 未定 |
| 54 | そのほか | 外部のツール向けに、ボードの集計（期限切れの数・今日と今週のタスク・完了率）を返す | 外部連携用のコントローラー | 外の AI からの操作 | 未定 |
| 55 | そのほか | 開発用に、試しの通知を自分に送る | `routes/web.php` | | 未定 |
| 56 | そのほか | トップページ（ログインの前の紹介） | `welcome.blade.php` | | 未定 |

### 新しく足すもの

| # | まとまり | 機能 | 今の作り | 要望との関係 | 扱い |
| --- | --- | --- | --- | --- | --- |
| N1 | 外の AI | アプリの外の AI から、タスクを登録・変更する | 無い | 外の AI からの操作 | 未定 |
| N2 | 表示 | 横軸でタスクを基準にしたタイムライン | 無い | タイムラインの横軸化 | 未定 |
