# S2J Slug Generater — PHPUnit (wp-env なし / Local 開発)

## 正本

MySQL / `svn` / `.env.tests`、開発 DB `local` を使わない方針などの共通手順は **[wp-plugin-spec: PHPUNIT_LOCAL.md](https://github.com/stein2nd/wp-plugin-spec/blob/main/docs/PHPUNIT_LOCAL.md)** を参照してください。

## テスト構成 (5件)

| 種別 | 件数 | 設定 | 主な対象 |
| --- | --- | --- | --- |
| ユニット | 4 | `phpunit.unit.xml.dist` | `includes/Domain/pure.php` (`tests/phpunit/Unit/PureDomainTest.php`)。**DB 不要** |
| 統合 | 1 | `phpunit.xml.dist` | REST `POST …/s2j-slug-generater/v1/generate` 登録 (`RestRouteTest`)。**`wordpress_test` のみ** |

## 実行コマンド

| コマンド | 件数 | 備考 |
| --- | --- | --- |
| `npm run test:unit:php:unit-only` | 4 | PHP + Composer のみ |
| `npm run test:unit:php:local` | 5 | `tests/smoke-local.sh`（初回 MySQL + svn で test lib 取得） |
| `composer test:unit` / `composer test:all` | 4 / 5 | Node 不要 |
| `npm run test:unit:php` | 5 | **CI**（MySQL サービス + `bin/phpunit-ci.sh`） |

```bash
composer install
cp .env.tests.example .env.tests   # 正本に従い編集
npm run test:unit:php:local
```

## 関連ファイル

| パス | 役割 |
| --- | --- |
| `phpunit.unit.xml.dist` / `phpunit.xml.dist` | ユニット / 統合 |
| `tests/bootstrap-unit.php` / `tests/bootstrap.php` | bootstrap |
| `tests/smoke-local.sh` | ローカル5件 |
| `bin/install-wp-tests.sh` | テスト lib + `wordpress_test` |
| `bin/phpunit-ci.sh` | CI 用5件 |
| `tests/.wordpress` / `tests/.wordpress-tests-lib` | 初回 install 成果物 (gitignore) |
