# S2J Slug Generater - REST API

← [索引](./specs.md)

REST は、サーバー側の **薄い Facade** (外枠) です。  
中身のビジネスロジックは、`generateCandidate` に委譲し、レスポンス形は `SPEC_mod.md` を踏襲して `GenerateResult` に対応づけます。

## エンドポイント

```text
POST /wp-json/s2j-slug-generater/v1/generate
```

### 入力

実装で WP REST の `X-WP-Nonce` に寄せる場合は、ボディ `nonce` と重複させない方針を、一文で固定します。要件は「nonce チェック必須」です。

```json
{
  "title": "記事タイトル",
  "nonce": "nonce文字列"
}
```

### 出力 (成功)

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
| `accepted` | `GenerateSuccess.accepted` (明示を推奨) |

閾値未満でも HTTP 成功として類似度を返し、`accepted: false` でクライアントが扱います。

### 出力 (失敗)

```json
{
  "success": false,
  "error": {
    "code": "missing_similarity_ai_key",
    "message": "..."
  }
}
```

`GenerateFailure` → HTTP JSON の写像は、Facade / 応答 Adapter の責務です。

## セキュリティ (外枠)

```text
authorize(request) =
  checkNonce(request)
    |> andThen(() =>
         if current_user_can('edit_posts') then Ok(unit) else Err(Forbidden)
       )
```

* nonce 必須、`edit_posts` 必須
* 翻訳キー / 類似度用 AI キーは、Option から読み、レスポンスに含めない

## Facade の骨格

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

Clean Coding: プロバイダ別の HTTP 詳細は、ここに書かない (Translation / Similarity Adapter の中に書く)。

次: [10-build-and-i18n.md](./10-build-and-i18n.md)
