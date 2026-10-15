<?php
/**
 * PHPUnit bootstrap for wp-env tests-cli.
 *
 * @package S2J_Slug_Generater
 */

define(
    'WP_TESTS_PHPUNIT_POLYFILLS_PATH',
    dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills'
);

$_tests_dir = getenv('WP_TESTS_DIR');
if (! $_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
    'muplugins_loaded',
    static function () {
        require dirname(__DIR__) . '/includes/Domain/pure.php';
    }
);

require $_tests_dir . '/includes/bootstrap.php';
