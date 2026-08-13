# S2J Slug Generater - プラグイン概要

← [索引](./specs.md)

## メタ情報

| 項目 | 値 |
| --- | --- |
| 名称 | S2J Slug Generater |
| プラグイン・スラッグ | `s2j-slug-generater` |
| テキスト・ドメイン | `s2j-slug-generater` |
| ライセンス | GPL v3以降 |

## 目的

投稿タイトルを入力とし、翻訳 API による英訳・逆翻訳と類似度評価を経て、**スラッグ候補** を生成します。ユーザーが候補を確認し、投稿のスラッグ欄に適用します。

## 特徴 (振る舞いの要約)

* Gutenberg: 文書設定サイドバーの `PluginDocumentSettingPanel` に UI を置く
* Classic: 投稿タイトル直下 (`edit_form_after_title`) にパネルを置き、Gutenberg と同じドメイン操作を再現する
* 管理画面で翻訳 API / 類似度用の設定を Option として保持する (PHP Settings API)
* 類似度は [S2J Similarity Service](https://github.com/stein2nd/s2j-similarity-service) (コサイン類似度) を用いる

## 設計スタンス

基本アプローチは、[specs.md](./specs.md) の通りです。Clean Architecture のフル構造は採りません。依存は外→内、内側は WP 非依存、という原則だけ借用します。

詳細は、[02-fp-principles.md](./02-fp-principles.md) を参照してください。

| 層 | 本プラグインでの担当 |
| --- | --- |
| Clean Coding | 命名、短さ、1責務。貢献者が追えるコードを最優先 |
| 外枠 (GoF デザインパターン / オブジェクト指向) | WP、HTTP、エディター状態など副作用の境界 |
| 中身 (関数型プログラミング) | 正規化・閾値・結果組立など、予測可能なビジネスロジック |
