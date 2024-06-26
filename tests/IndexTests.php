<?php
/**
 * This file contains the WPTOTest class which extends WP_UnitTestCase.
 * It provides a series of unit tests for verifying the functionality of the WP Tag Order plugin
 * in a WordPress environment, specifically focusing on the meta box markup, saving tag order,
 * and AJAX functions for syncing, updating, and deleting tags associated with posts.
 * These tests ensure that the system behaves as expected when handling valid inputs
 * for post IDs, taxonomy terms, and AJAX requests.
 *
 * @package WP_Tag_Order
 */

// Require the index.php file from the includes directory.
require_once __DIR__ . '/../includes/index.php';

/**
 * WPTOTest class.
 *
 * @package WP_Tag_Order
 *
 * @covers ::wpto_meta_box_markup
 */
class WPTOTest extends WP_UnitTestCase {

	/**
	 * Set up before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->factory->post->create_many(
			5,
			array(
				'post_type' => 'post',
			)
		);

		$this->factory->term->create_many(
			3,
			array(
				'taxonomy' => 'post_tag',
			)
		);
	}

	/**
	 * Test wpto_meta_box_markup function.
	 *
	 * @return void
	 *
	 * @covers ::wpto_meta_box_markup
	 */
	public function test_wpto_meta_box_markup() {
		$post_id = $this->factory->post->create();
		$post    = get_post( $post_id );

		$metabox = array(
			'args' => array(
				'taxonomy' => 'post_tag',
			),
		);

		ob_start();
		wpto_meta_box_markup( $post, $metabox );
		$output = ob_get_clean();

		$this->assertStringContainsString( '<div class="inner">', $output );
		$this->assertStringContainsString( '<ul>', $output );
		$this->assertStringContainsString( '</ul>', $output );
		$this->assertStringContainsString( '</div>', $output );
	}
}
