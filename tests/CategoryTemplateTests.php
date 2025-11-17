<?php
/**
 * This file contains the CategoryTemplateTests class which extends WP_UnitTestCase.
 * It provides a series of unit tests for verifying the functionality of category templates
 * in a WordPress environment, specifically focusing on the ordering and retrieval of terms
 * associated with posts. These tests ensure that the system behaves as expected when handling
 * valid and invalid inputs for post IDs and taxonomy terms.
 *
 * @package WP_Tag_Order
 */

// Require the category-template.php file.
require_once __DIR__ . '/../includes/category-template.php';

/**
 * Class CategoryTemplateTests
 *
 * @package WP_Tag_Order
 */
class CategoryTemplateTests extends WP_UnitTestCase {

	/**
	 * Post ID used for testing.
	 *
	 * @var int $post_id Holds the ID of the post created for testing.
	 */
	private $post_id;

	/**
	 * Set up the environment for each test.
	 *
	 * Creates a post and assigns tags to it for testing purposes.
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
		add_post_meta( $this->post_id, wp_tag_order_meta_key( 'post_tag' ), $serialized_tag_ids );
	}

	/**
	 * Clean up the environment after each test.
	 *
	 * Deletes the post created during the setup.
	 */
	public function tearDown(): void {
		// Delete the post.
		wp_delete_post( $this->post_id, true );

		parent::tearDown();
	}

	/**
	 * Test the ordered retrieval of terms for a valid post.
	 *
	 * @covers get_the_terms_ordered
	 */
	public function test_get_the_terms_ordered_valid() {
		$terms = get_the_terms_ordered( $this->post_id, 'post_tag' );

		$this->assertIsArray( $terms );
		$this->assertCount( 5, $terms );

		$expected_tags = array( 'Tag1', 'Tag2', 'Tag3', 'Tag4', 'Tag5' );
		$tag_names     = array_map(
			function ( $term ) {
				return $term->name;
			},
			$terms
		);

		$this->assertEquals( $expected_tags, $tag_names );
	}

	/**
	 * Test the ordered retrieval of terms for an invalid post ID.
	 *
	 * @covers get_the_terms_ordered
	 */
	public function test_get_the_terms_ordered_invalid_post() {
		$terms = get_the_terms_ordered( 999999, 'post_tag' ); // Assuming this ID does not exist.

		$this->assertFalse( $terms );
	}

	/**
	 * Test the ordered retrieval of terms for an invalid taxonomy.
	 *
	 * @covers get_the_terms_ordered
	 */
	public function test_get_the_terms_ordered_invalid_taxonomy() {
		$terms = get_the_terms_ordered( $this->post_id, 'nonexistent_taxonomy' );

		$this->assertFalse( $terms );
	}

	/**
	 * Test the ordered retrieval of tags for a valid post.
	 *
	 * @covers get_the_tags_ordered
	 */
	public function test_get_the_tags_ordered_valid_post() {
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

		$this->assertEquals( $expected_tags, $tag_names );
	}

	/**
	 * Test the ordered retrieval of tags for an invalid post ID.
	 *
	 * @covers get_the_tags_ordered
	 */
	public function test_get_the_tags_ordered_invalid_post() {
		$tags = get_the_tags_ordered( 999999 ); // Assuming this ID does not exist.

		// Check if the function returns false.
		$this->assertFalse( $tags );
	}

	/**
	 * Test the ordered list of tags for a specific post.
	 *
	 * @covers get_the_tag_list_ordered
	 */
	public function test_get_the_tag_list_ordered() {
		$tag_list = get_the_tag_list_ordered( '', ', ', '', $this->post_id );
		$this->assertIsString( $tag_list );
		$this->assertStringContainsString( 'Tag1', $tag_list );
	}

	/**
	 * Test the ordered list of terms with specific formatting.
	 *
	 * @covers get_the_term_list_ordered
	 */
	public function test_get_the_term_list_ordered_formatting() {
		$before = '<ul>';
		$sep    = '</li><li>';
		$after  = '</li></ul>';

		// Execute the function with formatting arguments.
		$term_list = get_the_term_list_ordered( $this->post_id, 'post_tag', $before, $sep, $after );

		// Build the expected output using the tags added in setUp().
		$expected_tags   = array( 'Tag1', 'Tag2', 'Tag3', 'Tag4', 'Tag5' );
		$expected_output = $before;
		foreach ( $expected_tags as $tag ) {
			$term             = get_term_by( 'name', $tag, 'post_tag' );
			$expected_output .= '<a href="' . esc_url( get_term_link( $term ) ) . '" rel="tag">' . $tag . '</a>';
			if ( end( $expected_tags ) !== $tag ) {
				$expected_output .= $sep;
			}
		}
		$expected_output .= $after;

		$this->assertEquals( $expected_output, $term_list );
	}

	/**
	 * Test the display of ordered tags on a post page.
	 *
	 * @covers the_tags_ordered
	 */
	public function test_the_tags_ordered() {
		$permalink = get_permalink( $this->post_id );
		$this->go_to( '/{$permalink}' ); // Set up the environment to simulate being on the post's page.

		ob_start();
		the_tags_ordered();
		$output = ob_get_clean();

		$this->assertIsString( $output );
		$this->assertStringContainsString( 'Tags:', $output );
	}

	/**
	 * Test the ordered list of terms for a specific post.
	 *
	 * @covers get_the_term_list_ordered
	 */
	public function test_get_the_term_list_ordered() {
		$term_list = get_the_term_list_ordered( $this->post_id, 'post_tag', '', ', ', '' );
		$this->assertIsString( $term_list );
		$this->assertStringContainsString( 'Tag1', $term_list );
	}

	/**
	 * Test the display of ordered terms on a post page.
	 *
	 * @covers the_terms_ordered
	 */
	public function test_the_terms_ordered() {
		ob_start();
		the_terms_ordered( $this->post_id, 'post_tag', '', ', ', '' );
		$output = ob_get_clean();
		$this->assertIsString( $output );
		$this->assertStringContainsString( 'Tag1', $output );
	}
}
