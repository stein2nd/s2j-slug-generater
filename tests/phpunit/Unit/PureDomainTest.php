<?php
/**
 * Pure domain helper tests (no WordPress DB).
 *
 * @package S2J_Slug_Generater
 */

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class PureDomainTest extends TestCase {

	public function test_validate_title_rejects_empty_string() {
		$result = s2j_sg_validate_title( '   ' );

		$this->assertFalse( $result['ok'] );
		$this->assertSame( 'empty_title', $result['error']['code'] );
	}

	public function test_to_percent_converts_ratio() {
		$this->assertSame( 80.0, s2j_sg_to_percent( 0.8 ) );
	}

	public function test_passes_threshold() {
		$this->assertTrue( s2j_sg_passes_threshold( 0.8, 0.8 ) );
		$this->assertFalse( s2j_sg_passes_threshold( 0.79, 0.8 ) );
	}

	public function test_normalize_to_slug() {
		$this->assertSame( 'hello-world', s2j_sg_normalize_to_slug( 'Hello World!' ) );
	}
}
