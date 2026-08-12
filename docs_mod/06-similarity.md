# S2J Slug Generater - 類似度

← [索引](./specs.md)

採用は **`SPEC_mod.md` 相当**: [S2J Similarity Service](https://github.com/stein2nd/s2j-similarity-service) のコサイン類似度です。

FOP: 比較の I/O は、Similarity Adapter (外枠)、パーセント変換・閾値判定は、純関数 (内側)。

## 外枠: Similarity Adapter

```text
compareSimilarity(req: SimilarityRequest)
  -> Effect<Result<SimilarityRatio, SimilarityError>>
```

実装イメージ (PHP):

* Composer: [`s2j/similarity-service`](https://packagist.org/packages/s2j/similarity-service) (`^2.0`)
* ライブラリの呼び出し詳細は、Adapter 内に閉じる
* `$result['similarity']` (0.0〜1.0) を `SimilarityRatio` として返す

| ドメイン | 入力 |
| --- | --- |
| `baseText` | 元タイトル |
| `targetText` | 逆翻訳テキスト |
| `apiKey` | `PluginConfig.similarityAiApiKey` |
| `model` | `PluginConfig.similarityAiModel` (default 例: `text-embedding-3-small`) |
| `language` | `PluginConfig.sourceLanguage` |
| `locale` | `PluginConfig.locale` (default: `ja_JP`) |

`similarityAiApiKey` が空なら `Err(MissingSimilarityAiKey)` となります。オーケストレータが `GenerateFailure` に写します。

旧オブジェクト指向プログラミング Strategy クラス階層は、必須としません。差し替えは、**関数の注入** (または Adapter の差し替え) で足ります。

## 内側: 純関数

```text
toPercent(ratio: SimilarityRatio): SimilarityPercent
  = ratio * 100

formatPercentLabel(percent: SimilarityPercent): string
  = 小数第2位までの "n.99" 形

passesThreshold(ratio: SimilarityRatio, threshold: Threshold): bool
  = ratio >= threshold
```

* 閾値の保持・比較は、常に **ratio (0.0〜1.0)**
* UI / REST は、**percent (0.0〜100.0)**

Clean Coding: これらの関数に HTTP や Option を混ぜません。

## 設定 UI 用の選択肢データ

* モデル例: `text-embedding-3-small` (default)、`text-embedding-3-large`、`text-embedding-ada-002`  
* ロケール例: `ja_JP` (default)、`en_US`、`fr_FR`

翻訳プロバイダ記述子とは独立した、類似度用の入力データです。

次: [07-admin.md](./07-admin.md)
