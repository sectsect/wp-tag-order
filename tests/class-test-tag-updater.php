<?php
/**
 * Test Tag_Updater Class
 *
 * @package WP_Tag_Order
 * @since 3.11.0
 */

declare(strict_types=1);

namespace WP_Tag_Order\Tests;

use WP_Tag_Order\Tag_Updater;
use WP_UnitTestCase;

/**
 * Test cases for Tag_Updater class
 */
class Test_Tag_Updater extends WP_UnitTestCase {
	/**
	 * Tag_Updater instance
	 *
	 * @var Tag_Updater
	 */
	private $tag_updater;

	/**
	 * Test taxonomy name
	 *
	 * @var string
	 */
	private $test_taxonomy;

	/**
	 * Setup before each test.
	 * Enables specific taxonomy for the plugin.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->tag_updater   = new Tag_Updater();
		$this->test_taxonomy = 'post_tag';

		// Ensure the test taxonomy is enabled for the plugin.
		add_filter( 'wto_is_enabled_taxonomy', array( $this, 'enable_test_taxonomy' ), 10, 1 );

		// Register test taxonomy if not already registered.
		if ( ! taxonomy_exists( $this->test_taxonomy ) ) {
			register_taxonomy( $this->test_taxonomy, 'post' );
		}

		// Enable specific taxonomy for the test.
		$enabled_taxonomies = array( 'post_tag' );
		update_option( 'wpto_enabled_taxonomies', $enabled_taxonomies );
	}

	/**
	 * Teardown after each test.
	 * Resets the enabled taxonomies option.
	 */
	protected function tearDown(): void {
		// Remove the filter after the test.
		remove_filter( 'wto_is_enabled_taxonomy', array( $this, 'enable_test_taxonomy' ) );

		// Reset the enabled taxonomies option.
		delete_option( 'wpto_enabled_taxonomies' );

		parent::tearDown();
	}

	/**
	 * Mock method to enable test taxonomy for the plugin
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return bool
	 */
	public function enable_test_taxonomy( $taxonomy ): bool {
		return $taxonomy === $this->test_taxonomy;
	}

	/**
	 * Test successful tag order update
	 *
	 * @covers Tag_Updater::update_tag_order
	 */
	public function test_update_tag_order_with_array(): void {
		// Create a test post.
		$post_id = $this->factory()->post->create();

		// Create test tags.
		$tag_ids = array(
			$this->factory()->term->create( array( 'taxonomy' => $this->test_taxonomy ) ),
			$this->factory()->term->create( array( 'taxonomy' => $this->test_taxonomy ) ),
			$this->factory()->term->create( array( 'taxonomy' => $this->test_taxonomy ) ),
		);

		// Update tag order.
		$result = $this->tag_updater->update_tag_order( $post_id, $this->test_taxonomy, $tag_ids );

		$this->assertNotFalse( $result, 'Failed to update metadata for tag order' );

		// Verify meta was saved correctly.
		$saved_tags = unserialize( get_post_meta( $post_id, 'wp-tag-order-' . $this->test_taxonomy, true ) );
		$this->assertSame( $tag_ids, $saved_tags );
	}

	/**
	 * Test tag order update with comma-separated string
	 *
	 * @covers Tag_Updater::update_tag_order
	 */
	public function test_update_tag_order_with_string(): void {
		// Create a test post.
		$post_id = $this->factory()->post->create();

		// Create test tags.
		$tag_ids = array(
			$this->factory()->term->create( array( 'taxonomy' => $this->test_taxonomy ) ),
			$this->factory()->term->create( array( 'taxonomy' => $this->test_taxonomy ) ),
		);

		$tag_ids_string = implode( ',', $tag_ids );

		// Update tag order.
		$result = $this->tag_updater->update_tag_order( $post_id, $this->test_taxonomy, $tag_ids_string );

		$this->assertNotFalse( $result, 'Failed to update metadata for tag order' );

		// Verify meta was saved correctly.
		$saved_tags = unserialize( get_post_meta( $post_id, 'wp-tag-order-' . $this->test_taxonomy, true ) );
		$this->assertSame( $tag_ids, $saved_tags );
	}

	/**
	 * Test invalid post ID throws exception
	 *
	 * @covers Tag_Updater::update_tag_order
	 */
	public function test_update_tag_order_with_invalid_post_id(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid post ID' );

		$this->tag_updater->update_tag_order( 0, $this->test_taxonomy, array( 1 ) );
	}

	/**
	 * Test empty taxonomy throws exception
	 *
	 * @covers Tag_Updater::update_tag_order
	 */
	public function test_update_tag_order_with_empty_taxonomy(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Taxonomy cannot be empty' );

		$post_id = $this->factory()->post->create();
		$this->tag_updater->update_tag_order( $post_id, '', array( 1 ) );
	}

	/**
	 * Test non-existent taxonomy throws exception
	 *
	 * @covers Tag_Updater::update_tag_order
	 */
	public function test_update_tag_order_with_non_existent_taxonomy(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid taxonomy: non_existent_taxonomy' );

		$post_id = $this->factory()->post->create();
		$this->tag_updater->update_tag_order( $post_id, 'non_existent_taxonomy', array( 1 ) );
	}

	/**
	 * Test empty tag IDs throws exception
	 *
	 * @covers Tag_Updater::update_tag_order
	 */
	public function test_update_tag_order_with_empty_tag_ids(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Tag IDs cannot be empty' );

		$post_id = $this->factory()->post->create();
		$this->tag_updater->update_tag_order( $post_id, $this->test_taxonomy, array() );
	}

	/**
	 * Test invalid tag ID throws exception
	 *
	 * @covers Tag_Updater::update_tag_order
	 */
	public function test_update_tag_order_with_invalid_tag_id(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid tag ID: invalid' );

		$post_id = $this->factory()->post->create();
		$this->tag_updater->update_tag_order( $post_id, $this->test_taxonomy, array( 'invalid' ) );
	}
}
