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
		$this->post_id = wp_insert_post(
			array(
				'post_title'   => 'Test Post',
				'post_content' => 'This is a test post.',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'post',
			)
		);

		// Add tags to the post.
		wp_set_post_tags( $this->post_id, array( 'Tag1', 'Tag2', 'Tag3', 'Tag4', 'Tag5' ), true );

		// Retrieve all assigned tags to get their term_ids.
		$tags    = wp_get_post_tags( $this->post_id );
		$tag_ids = array_map(
			function ( $tag ) {
				return $tag->term_id;
			},
			$tags
		);

		// Serialize the array of tag IDs.
		$serialized_tag_ids = serialize( $tag_ids );

		// Insert the serialized tag IDs into the wp_postmeta table with a custom key.
		add_post_meta( $this->post_id, 'wp-tag-order-post_tag', $serialized_tag_ids );
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
	 * Test get_the_terms_ordered function with valid input.
	 */
	public function test_get_the_terms_ordered_valid() {
		$terms = get_the_terms_ordered($this->post_id, 'post_tag');

		$this->assertIsArray($terms);

		$this->assertCount(5, $terms);

		$expected_tags = array( 'Tag1', 'Tag2', 'Tag3', 'Tag4', 'Tag5' );
		$tag_names = array_map(function ($term) {
			return $term->name;
		}, $terms);

		$this->assertEquals($expected_tags, $tag_names);
	}

	/**
	 * Test get_the_terms_ordered function with invalid post ID.
	 */
	public function test_get_the_terms_ordered_invalid_post() {
		$terms = get_the_terms_ordered(999999, 'post_tag'); // Assuming this ID does not exist.

		$this->assertFalse($terms);
	}

	/**
	 * Test get_the_terms_ordered function with invalid taxonomy.
	 */
	public function test_get_the_terms_ordered_invalid_taxonomy() {
		$terms = get_the_terms_ordered($this->post_id, 'nonexistent_taxonomy');

		$this->assertFalse($terms);
	}

	/**
	 * @covers get_the_tags_ordered with a valid post.
	 */
	public function test_get_the_tags_ordered_valid_post() {
		// Check if the function exists
		$this->assertTrue( function_exists( 'get_the_tags_ordered' ), 'The function get_the_tags_ordered does not exist.' );

		$tags = get_the_tags_ordered( $this->post_id );

		// Check if the function returns an array of tags.
		$this->assertIsArray( $tags );

		// Check the count of tags.
		$this->assertCount( 5, $tags );

		// Check for correct order and data type.
		$expected_tags = array( 'Tag1', 'Tag2', 'Tag3', 'Tag4', 'Tag5' );
		$tag_names     = array_map(
			function ( $tag ) {
				return $tag->name;
			},
			$tags
		);

		$this->assertEquals( $expected_tags, $tag_names, 'Failed asserting the correct order of tags.' );
	}

	/**
	 * @covers get_the_tags_ordered with an invalid post.
	 */
	public function test_get_the_tags_ordered_invalid_post() {
		$tags = get_the_tags_ordered( 999999 ); // Assuming this ID does not exist.

		// Check if the function returns false.
		$this->assertFalse( $tags );
	}

	/**
	 * @covers get_the_tag_list_ordered function.
	 */
	public function test_get_the_tag_list_ordered() {
		$tag_list = get_the_tag_list_ordered('', ', ', '', $this->post_id);
		$this->assertIsString($tag_list);
		$this->assertStringContainsString('Tag1', $tag_list);
	}

	/**
	 * @covers the_tags_ordered function.
	 */
	// public function test_the_tags_ordered() {
	// 	ob_start();
	// 	the_tags_ordered(null, ', ', '', $this->post_id);
	// 	$output = ob_get_clean();
	// 	$this->assertIsString($output);
	// 	$this->assertStringContainsString('Tags:', $output);
	// }

	/**
	 * @covers get_the_term_list_ordered function.
	 */
	public function test_get_the_term_list_ordered() {
		$term_list = get_the_term_list_ordered($this->post_id, 'post_tag', '', ', ', '');
		$this->assertIsString($term_list);
		$this->assertStringContainsString('Tag1', $term_list);
	}

	/**
	 * @covers the_terms_ordered function.
	 */
	public function test_the_terms_ordered() {
		ob_start();
		the_terms_ordered($this->post_id, 'post_tag', '', ', ', '');
		$output = ob_get_clean();
		$this->assertIsString($output);
		$this->assertStringContainsString('Tag1', $output);
	}
}
