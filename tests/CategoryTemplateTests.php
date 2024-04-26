<?php
/**
 * Class CategoryTemplateTests
 *
 * @package WP_Tag_Order
 */

require_once __DIR__ . '/../includes/category-template.php';

class CategoryTemplateTests extends WP_UnitTestCase {

	/**
	 * Post ID for testing.
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * Set up the environment for each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Create a post using wp_insert_post.
		$this->post_id = wp_insert_post([
			'post_title'    => 'Test Post',
			'post_content'  => 'This is a test post.',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type'     => 'post'
		]);

		// Add tags to the post.
		wp_set_post_tags( $this->post_id, ['Tag1', 'Tag2', 'Tag3', 'Tag4', 'Tag5'], true );
	}

	/**
	 * Clean up the environment after each test.
	 */
	public function tearDown(): void {
		// Delete the post.
		wp_delete_post( $this->post_id, true );

		parent::tearDown();
	}

	/**
	 * @covers get_the_tags_ordered with a valid post.
	 */
	public function test_get_the_tags_ordered_valid_post() {
		$tags = get_the_tags_ordered( $this->post_id );

		// Check if the function returns an array of tags.
		$this->assertIsArray( $tags );

		// Check the count of tags.
		$this->assertCount( 5, $tags );

		// Check for correct order and data type.
		$expected_tags = ['Tag1', 'Tag2', 'Tag3', 'Tag4', 'Tag5'];
		$tag_names = array_map(function ($tag) {
			return $tag->name;
		}, $tags);

		$this->assertEquals( $expected_tags, $tag_names );
	}

	/**
	 * @covers get_the_tags_ordered with an invalid post.
	 */
	public function test_get_the_tags_ordered_invalid_post() {
		$tags = get_the_tags_ordered( 999999 ); // Assuming this ID does not exist.

		// Check if the function returns false.
		$this->assertFalse( $tags );
	}
}
