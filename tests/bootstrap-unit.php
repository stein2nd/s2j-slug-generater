<?php
/**
 * PHPUnit bootstrap for pure unit tests (no WordPress test suite / DB).
 *
 * @package S2J_Slug_Generater
 */

define( 'TESTS_PLUGIN_DIR', dirname( __DIR__ ) );

$polyfills = TESTS_PLUGIN_DIR . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
if ( file_exists( $polyfills ) ) {
	require_once $polyfills;
}

require_once TESTS_PLUGIN_DIR . '/phpstan-bootstrap.php';

if ( ! function_exists( '__' ) ) {
	/**
	 * Minimal i18n stub for domain-only tests.
	 *
	 * @param string $text   Text.
	 * @param string $domain Text domain.
	 * @return string
	 */
	function __( $text, $domain = 'default' ) {
		unset( $domain );
		return $text;
	}
}

require_once TESTS_PLUGIN_DIR . '/includes/Domain/pure.php';
