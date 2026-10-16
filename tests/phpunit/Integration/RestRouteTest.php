<?php
/**
 * WordPress integration tests (REST registration).
 *
 * @package S2J_Slug_Generater
 */

require_once dirname( __DIR__, 2 ) . '/stubs/wordpress-test-stubs.php';

/**
 * @coversNothing
 */
class RestRouteTest extends WP_UnitTestCase {

	public function test_generate_route_is_registered() {
		do_action( 'rest_api_init' );

		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/s2j-slug-generater/v1/generate', $routes );
	}
}
