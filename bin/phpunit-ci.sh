#!/usr/bin/env bash
# Run full PHPUnit suite (unit + integration) for CI or local after install-wp-tests.
set -euo pipefail
cd "$(dirname "$0")/.."
vendor/bin/phpunit -c phpunit.unit.xml.dist
vendor/bin/phpunit -c phpunit.xml.dist
