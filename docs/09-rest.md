# S2J Slug Generater - REST API

← [索引](./specs.md)

REST は、サーバー側の **薄い Facade** (外枠) です。中身のビジネスロジックは、`generateCandidate` に委譲し、レスポンス形は `docs/archive/SPEC_mod.md` を踏襲して `GenerateResult` に対応づけます。

## エンドポイント

```text
POST /wp-json/s2j-slug-generater/v1/generate
```

### 入力

認可は、**`X-WP-Nonce` (`wp_rest`)** に一本化します。ボディに `nonce` は置きません (重複させない)。

```json
{
  "title": "記事タイトル"
}
```

### 出力 (成功) — `HTTP 200`

閾値未満でも `HTTP 200` として類似度を返し、`accepted: false` でクライアントがスラッグ化を無効化します。

```json
{
  "success": true,
  "data": {
    "candidates": ["translated title or slug-oriented candidate"],
    "similarity": 85.5,
    "translated_title": "translated title",
    "accepted": true
  }
}
```

| フィールド | ドメイン |
| --- | --- |
| `translated_title` | `GenerateSuccess.translatedTitle` |
| `similarity` | percent (0.0〜100.0) |
| `candidates` | `GenerateSuccess.candidates` |
| `accepted` | `GenerateSuccess.accepted` (**必須**) |

### 出力 (失敗) — ドメインエラーは `HTTP 400`

`GenerateFailure` → HTTP JSON の写像は、Facade / 応答 Adapter の責務です。

```json
{
  "success": false,
  "error": {
    "code": "missing_similarity_ai_key",
    "message": "..."
  }
}
```

主な `code` は下記のとおりです。

| code | 意味 |
| --- | --- |
| `empty_title` | タイトル空 |
| `missing_translation_api_key` | 翻訳 API キー未設定 |
| `missing_similarity_ai_key` | 類似度用 AI キー未設定 |
| `invalid_api_key` | 翻訳プロバイダがキーを拒否 (DeepL は診断メッセージ付き可) |

### 認可失敗 — `HTTP 403`

permission callback が、`WP_Error` を返します (`invalid_nonce` / `forbidden`)。

## セキュリティ (外枠)

* `X-WP-Nonce` 必須、`edit_posts` 必須
* 翻訳キー / 類似度用 AI キーは、Option から読み、レスポンスに含めない

```text
authorize(request) =
  verify X-WP-Nonce against "wp_rest"
    |> andThen(() =>
         if current_user_can('edit_posts') then Ok(unit) else Err(Forbidden)
       )
```

## Facade の骨格

* Clean Coding: プロバイダ別の HTTP 詳細は、ここに書かない (Translation / Similarity Adapter の中に書く)。

```text
handleGenerate(request) =
  authorize(request)
    |> andThen(() => parseTitle(request))
    |> andThen(title =>
         loadConfig()
           |> andThen(config => generateCandidate(title, config, deps))
       )
    |> map(toHttpResponse)
```
