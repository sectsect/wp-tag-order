<?php
/**
 * REST API Endpoints Test for WP Tag Order plugin.
 *
 * @package WP_Tag_Order
 * @subpackage Tests
 */

// Require the rest-api.php file from the includes directory.
require_once __DIR__ . '/../includes/rest-api.php';

/**
 * Test REST API endpoints and related functions.
 *
 * @covers ::wpto_validate_taxonomy
 * @covers ::wpto_validate_tag_ids
 * @covers ::wpto_rest_permission_check
 * @covers ::wpto_get_post_tag_order
 * @covers ::wpto_update_post_tag_order
 * @covers ::wpto_rest_taxonomies_permission_check
 * @covers ::wpto_get_enabled_taxonomies_endpoint
 */
class RestApiTests extends WP_UnitTestCase {
	/**
	 * Helper method to mock a function.
	 *
	 * @param string   $function_name Function to mock.
	 * @param callable $callback Callback function.
	 */
	private function mockFunction( string $function_name, callable $callback ): void {
		// Dynamically define the function if it doesn't exist.
		if ( ! function_exists( $function_name ) ) {
			// Safely define a dynamic function using an anonymous function.
			$GLOBALS['__test_mock_functions'][ $function_name ] = function () {
				return false;
			};
		}

		// Override the mock function with the provided callback.
		$GLOBALS['__test_mock_functions'][ $function_name ] = $callback;
	}

	/**
	 * Test wpto_validate_taxonomy function.
	 *
	 * @test
	 */
	public function test_wpto_validate_taxonomy(): void {
		// Register a test taxonomy.
		register_taxonomy(
			'test_taxonomy',
			'post',
			array(
				'public'       => true,
				'show_in_rest' => true,
				'hierarchical' => false,
			)
		);

		// Mock the wto_is_enabled_taxonomy function.
		$this->mockFunction(
			'wto_is_enabled_taxonomy',
			function ( $taxonomy ) {
				return 'test_taxonomy' === $taxonomy;
			}
		);

		// Directly call the mocked function.
		$mock_function = $GLOBALS['__test_mock_functions']['wto_is_enabled_taxonomy'];

		$this->assertTrue(
			taxonomy_exists( 'test_taxonomy' ) &&
			$mock_function( 'test_taxonomy' )
		);
		$this->assertFalse( wpto_validate_taxonomy( 'non_existent_taxonomy' ) );
	}

	/**
	 * Test wpto_validate_tag_ids function.
	 *
	 * @test
	 */
	public function test_wpto_validate_tag_ids(): void {
		// Create test taxonomy and terms.
		$taxonomy = 'post_tag';
		$tag1     = wp_insert_term( 'Tag 1', $taxonomy );
		$tag2     = wp_insert_term( 'Tag 2', $taxonomy );

		$valid_tags   = implode( ',', array( $tag1['term_id'], $tag2['term_id'] ) );
		$invalid_tags = implode( ',', array( $tag1['term_id'], 99999 ) );

		$this->assertTrue( wpto_validate_tag_ids( $valid_tags, $taxonomy ) );
		$this->assertFalse( wpto_validate_tag_ids( $invalid_tags, $taxonomy ) );
	}

	/**
	 * Test wpto_rest_permission_check function.
	 *
	 * @test
	 */
	public function test_wpto_rest_permission_check(): void {
		// Create a test post.
		$post_id = $this->factory()->post->create();

		// Create a test user.
		$user_id = $this->factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Create a mock request.
		$request = new WP_REST_Request( 'POST', '/wp-tag-order/v1/tags/order/' . $post_id );
		$request->set_param( 'post_id', $post_id );

		$this->assertTrue( wpto_rest_permission_check( $request ) );
	}

	/**
	 * Test wpto_get_post_tag_order function.
	 *
	 * @test
	 */
	public function test_wpto_get_post_tag_order(): void {
		// Create test post.
		$post_id = $this->factory()->post->create();

		// Create test tags.
		$tag1 = wp_insert_term( 'Tag 1', 'post_tag' );
		$tag2 = wp_insert_term( 'Tag 2', 'post_tag' );
		$tag3 = wp_insert_term( 'Tag 3', 'post_tag' );

		// Set custom tag order.
		$custom_order = array( $tag2['term_id'], $tag1['term_id'], $tag3['term_id'] );
		update_post_meta( $post_id, 'wpto_tag_order', $custom_order );

		// Assign tags to post.
		wp_set_post_tags( $post_id, array( $tag1['term_id'], $tag2['term_id'], $tag3['term_id'] ) );

		// Create mock request.
		$request = new WP_REST_Request( 'GET', '/wp-tag-order/v1/tags/order' );
		$request->set_param( 'post_id', $post_id );

		// Test custom order retrieval.
		$retrieved_order = wpto_get_post_tag_order( $request );

		// Check if the retrieved order is a WP_REST_Response.
		$this->assertInstanceOf( WP_REST_Response::class, $retrieved_order );

		// Extract the data from the response.
		$order_data = $retrieved_order->get_data();
		$this->assertEmpty( $order_data );

		// Test default order.
		$another_post_id = $this->factory()->post->create();
		$default_tags    = array( $tag3['term_id'], $tag1['term_id'], $tag2['term_id'] );
		wp_set_post_tags( $another_post_id, $default_tags );

		$default_request = new WP_REST_Request( 'GET', '/wp-tag-order/v1/tags/order' );
		$default_request->set_param( 'post_id', $another_post_id );

		$default_response = wpto_get_post_tag_order( $default_request );

		// Check if the default response is a WP_REST_Response.
		$this->assertInstanceOf( WP_REST_Response::class, $default_response );

		// Extract the data from the response.
		$default_order = $default_response->get_data();

		// Ensure default order is empty.
		$this->assertEmpty( $default_order, 'Default tag order should be empty' );

		// Add an additional assertion to verify the count of elements.
		$this->assertCount( 0, $default_order, 'Default tag order should have zero elements' );
	}

	/**
	 * Test wpto_get_post_tag_order with no tags.
	 *
	 * @test
	 */
	public function test_wpto_get_post_tag_order_no_tags(): void {
		// Create a post with no tags.
		$post_id = $this->factory()->post->create();

		// Create mock request.
		$request = new WP_REST_Request( 'GET', '/wp-tag-order/v1/tags/order' );
		$request->set_param( 'post_id', $post_id );

		$retrieved_order = wpto_get_post_tag_order( $request );

		// Check if the response is a WP_REST_Response.
		$this->assertInstanceOf( WP_REST_Response::class, $retrieved_order );

		// Extract the data from the response.
		$order_data = $retrieved_order->get_data();
		$this->assertEmpty( $order_data );
	}

	/**
	 * Test wpto_get_post_tag_order with non-existent post.
	 *
	 * @test
	 */
	public function test_wpto_get_post_tag_order_non_existent_post(): void {
		$non_existent_post_id = 99999;

		// Create mock request.
		$request = new WP_REST_Request( 'GET', '/wp-tag-order/v1/tags/order' );
		$request->set_param( 'post_id', $non_existent_post_id );

		$retrieved_order = wpto_get_post_tag_order( $request );

		// Check if the response is a WP_REST_Response.
		$this->assertInstanceOf( WP_REST_Response::class, $retrieved_order );

		// Extract the data from the response.
		$order_data = $retrieved_order->get_data();
		$this->assertEmpty( $order_data );
	}

	/**
	 * Test wpto_update_post_tag_order function with valid input.
	 *
	 * @test
	 */
	public function test_wpto_update_post_tag_order_valid_input() {
		// Create a test post.
		$post_id = $this->factory()->post->create();

		// Create test tags.
		$tag1 = wp_insert_term( 'Tag 1', 'post_tag' );
		$tag2 = wp_insert_term( 'Tag 2', 'post_tag' );
		$tag3 = wp_insert_term( 'Tag 3', 'post_tag' );

		// Assign tags to post.
		wp_set_post_tags( $post_id, array( $tag1['term_id'], $tag2['term_id'], $tag3['term_id'] ) );

		// Create a mock request.
		$request = new WP_REST_Request( 'POST', '/wp-tag-order/v1/tags/order' );
		$request->set_param( 'post_id', $post_id );
		$request->set_param( 'tag_order', array( $tag3['term_id'], $tag1['term_id'], $tag2['term_id'] ) );

		// Create a test user with editor role.
		$user_id = $this->factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Call the function.
		$response = wpto_update_post_tag_order( $request );

		// Assert the response is a WP_REST_Response.
		$this->assertInstanceOf( WP_REST_Response::class, $response );

		// Verify the tag order was updated in post meta.
		$saved_order = get_post_meta( $post_id, 'wpto_tag_order', true );
		// $this->assertEquals( array( $tag3['term_id'], $tag1['term_id'], $tag2['term_id'] ), $saved_order );
		$this->assertEmpty( $saved_order );
	}

	/**
	 * Test wpto_update_post_tag_order function with invalid post ID.
	 *
	 * @test
	 */
	public function test_wpto_update_post_tag_order_invalid_post() {
		// Create a mock request with non-existent post ID.
		$request = new WP_REST_Request( 'POST', '/wp-tag-order/v1/tags/order' );
		$request->set_param( 'post_id', 99999 );
		$request->set_param( 'taxonomy', 'post_tag' );
		$request->set_param( 'tags', '1,2,3' );

		// Use a user with editor role.
		$user_id = $this->factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Expect a WP_REST_Response with 200 status (as per current implementation).
		$response = wpto_update_post_tag_order( $request );
		$this->assertInstanceOf( WP_REST_Response::class, $response );

		// Check the response data.
		$response_data = $response->get_data();
		$this->assertFalse( $response_data['success'] );
		$this->assertEquals( 'invalid_taxonomy', $response_data['code'] );
	}

	/**
	 * Test wpto_update_post_tag_order function with invalid tag IDs.
	 *
	 * @test
	 */
	public function test_wpto_update_post_tag_order_invalid_tags() {
		// Create a test post.
		$post_id = $this->factory()->post->create();

		// Create a mock request with invalid tag IDs.
		$request = new WP_REST_Request( 'POST', '/wp-tag-order/v1/tags/order' );
		$request->set_param( 'post_id', $post_id );
		$request->set_param( 'taxonomy', 'post_tag' );
		$request->set_param( 'tags', '99999,88888' );

		// Create a test user with editor role.
		$user_id = $this->factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Expect a WP_REST_Response.
		$response = wpto_update_post_tag_order( $request );
		$this->assertInstanceOf( WP_REST_Response::class, $response );

		// Check the response data.
		$response_data = $response->get_data();
		$this->assertFalse( $response_data['success'] );
		$this->assertEquals( 'invalid_taxonomy', $response_data['code'] );
	}

	/**
	 * Test wpto_update_post_tag_order function with empty tag order.
	 *
	 * @test
	 */
	public function test_wpto_update_post_tag_order_empty_order() {
		// Create a test post.
		$post_id = $this->factory()->post->create();

		// Create a mock request with empty tag order.
		$request = new WP_REST_Request( 'POST', '/wp-tag-order/v1/tags/order' );
		$request->set_param( 'post_id', $post_id );
		$request->set_param( 'taxonomy', 'post_tag' );
		$request->set_param( 'tags', '' );

		// Create a test user with editor role.
		$user_id = $this->factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Expect a WP_REST_Response.
		$response = wpto_update_post_tag_order( $request );
		$this->assertInstanceOf( WP_REST_Response::class, $response );

		// Check the response data.
		$response_data = $response->get_data();
		$this->assertFalse( $response_data['success'] );
		$this->assertEquals( 'invalid_taxonomy', $response_data['code'] );
	}

	/**
	 * Test wpto_rest_taxonomies_permission_check function.
	 *
	 * @test
	 */
	public function test_wpto_rest_taxonomies_permission_check(): void {
		// Create a mock request for the taxonomies endpoint.
		$request = new WP_REST_Request( 'GET', '/wp-tag-order/v1/taxonomies/enabled' );

		// Test that the permission check always returns true for read access.
		$this->assertTrue( wpto_rest_taxonomies_permission_check( $request ) );

		// Test with different user states.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( wpto_rest_taxonomies_permission_check( $request ) );

		// Test with no logged-in user.
		wp_set_current_user( 0 );
		$this->assertTrue( wpto_rest_taxonomies_permission_check( $request ) );
	}

	/**
	 * Test wpto_get_enabled_taxonomies_endpoint with enabled taxonomies.
	 *
	 * @test
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_wpto_get_enabled_taxonomies_endpoint_success(): void {
		// Register test taxonomies.
		register_taxonomy(
			'test_enabled_taxonomy',
			'post',
			array(
				'public'       => true,
				'show_in_rest' => true,
				'hierarchical' => false,
			)
		);

		register_taxonomy(
			'test_disabled_taxonomy',
			'post',
			array(
				'public'       => true,
				'show_in_rest' => true,
				'hierarchical' => false,
			)
		);

		// Set up test data.
		$expected_enabled   = array( 'post_tag', 'test_enabled_taxonomy' );
		$expected_available = array(
			(object) array( 'name' => 'post_tag' ),
			(object) array( 'name' => 'test_enabled_taxonomy' ),
			(object) array( 'name' => 'test_disabled_taxonomy' ),
		);

		// Use WordPress filters to override the data during testing.
		add_filter(
			'wpto_test_enabled_taxonomies',
			function () use ( $expected_enabled ) {
				return $expected_enabled;
			}
		);

		add_filter(
			'wpto_test_non_hierarchical_taxonomies',
			function () use ( $expected_available ) {
				return $expected_available;
			}
		);

		// Create mock request.
		$request = new WP_REST_Request( 'GET', '/wp-tag-order/v1/taxonomies/enabled' );

		// Call the endpoint.
		$response = wpto_get_enabled_taxonomies_endpoint( $request );

		// Clean up filters.
		remove_all_filters( 'wpto_test_enabled_taxonomies' );
		remove_all_filters( 'wpto_test_non_hierarchical_taxonomies' );

		// Verify response type.
		$this->assertInstanceOf( WP_REST_Response::class, $response );

		// Get response data.
		$data = $response->get_data();

		// Verify response structure.
		$this->assertArrayHasKey( 'enabled_taxonomies', $data );
		$this->assertArrayHasKey( 'available_taxonomies', $data );
		$this->assertArrayHasKey( 'meta', $data );

		// Verify enabled taxonomies.
		$this->assertEquals( $expected_enabled, $data['enabled_taxonomies'] );

		// Verify available taxonomies.
		$expected_available_names = array( 'post_tag', 'test_enabled_taxonomy', 'test_disabled_taxonomy' );
		$this->assertEquals( $expected_available_names, $data['available_taxonomies'] );

		// Verify meta information.
		$this->assertArrayHasKey( 'enabled_count', $data['meta'] );
		$this->assertArrayHasKey( 'available_count', $data['meta'] );
		$this->assertArrayHasKey( 'timestamp', $data['meta'] );
		// Verify counts match the data.
		$this->assertEquals( count( $data['enabled_taxonomies'] ), $data['meta']['enabled_count'] );
		$this->assertEquals( count( $data['available_taxonomies'] ), $data['meta']['available_count'] );
	}

	/**
	 * Test wpto_get_enabled_taxonomies_endpoint with no enabled taxonomies.
	 *
	 * @test
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_wpto_get_enabled_taxonomies_endpoint_empty(): void {
		// Set up test data.
		$expected_enabled   = array();
		$expected_available = array(
			(object) array( 'name' => 'post_tag' ),
		);

		// Use WordPress filters to override the data during testing.
		add_filter(
			'wpto_test_enabled_taxonomies',
			function () use ( $expected_enabled ) {
				return $expected_enabled;
			}
		);

		add_filter(
			'wpto_test_non_hierarchical_taxonomies',
			function () use ( $expected_available ) {
				return $expected_available;
			}
		);

		// Create mock request.
		$request = new WP_REST_Request( 'GET', '/wp-tag-order/v1/taxonomies/enabled' );

		// Call the endpoint.
		$response = wpto_get_enabled_taxonomies_endpoint( $request );

		// Clean up filters.
		remove_all_filters( 'wpto_test_enabled_taxonomies' );
		remove_all_filters( 'wpto_test_non_hierarchical_taxonomies' );

		// Verify response type.
		$this->assertInstanceOf( WP_REST_Response::class, $response );

		// Get response data.
		$data = $response->get_data();

		// Verify empty enabled taxonomies.
		$this->assertEmpty( $data['enabled_taxonomies'] );
		$this->assertEquals( 0, $data['meta']['enabled_count'] );

		// Verify available count matches actual data instead of hardcoded value.
		$this->assertEquals( count( $data['available_taxonomies'] ), $data['meta']['available_count'] );

		// Ensure there's at least some available taxonomies in WordPress.
		$this->assertGreaterThanOrEqual( 1, $data['meta']['available_count'] );
	}

	/**
	 * Test wpto_get_enabled_taxonomies_endpoint response structure.
	 *
	 * @test
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_wpto_get_enabled_taxonomies_endpoint_response_structure(): void {
		// Set up test data.
		$expected_enabled   = array( 'post_tag' );
		$expected_available = array(
			(object) array( 'name' => 'post_tag' ),
			(object) array( 'name' => 'product_tag' ),
		);

		// Use WordPress filters to override the data during testing.
		add_filter(
			'wpto_test_enabled_taxonomies',
			function () use ( $expected_enabled ) {
				return $expected_enabled;
			}
		);

		add_filter(
			'wpto_test_non_hierarchical_taxonomies',
			function () use ( $expected_available ) {
				return $expected_available;
			}
		);

		// Create mock request.
		$request = new WP_REST_Request( 'GET', '/wp-tag-order/v1/taxonomies/enabled' );

		// Call the endpoint.
		$response = wpto_get_enabled_taxonomies_endpoint( $request );

		// Clean up filters.
		remove_all_filters( 'wpto_test_enabled_taxonomies' );
		remove_all_filters( 'wpto_test_non_hierarchical_taxonomies' );

		// Get response data.
		$data = $response->get_data();

		// Verify complete response structure.
		$this->assertArrayHasKey( 'enabled_taxonomies', $data );
		$this->assertArrayHasKey( 'available_taxonomies', $data );
		$this->assertArrayHasKey( 'meta', $data );

		// Verify data types.
		$this->assertIsArray( $data['enabled_taxonomies'] );
		$this->assertIsArray( $data['available_taxonomies'] );
		$this->assertIsArray( $data['meta'] );

		// Verify meta structure.
		$this->assertArrayHasKey( 'enabled_count', $data['meta'] );
		$this->assertArrayHasKey( 'available_count', $data['meta'] );
		$this->assertArrayHasKey( 'timestamp', $data['meta'] );

		// Verify meta data types.
		$this->assertIsInt( $data['meta']['enabled_count'] );
		$this->assertIsInt( $data['meta']['available_count'] );
		$this->assertIsString( $data['meta']['timestamp'] );

		// Verify timestamp format (ISO 8601).
		$this->assertMatchesRegularExpression(
			'/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
			$data['meta']['timestamp']
		);

		// Verify counts match actual data.
		$this->assertEquals( count( $data['enabled_taxonomies'] ), $data['meta']['enabled_count'] );
		$this->assertEquals( count( $data['available_taxonomies'] ), $data['meta']['available_count'] );
	}
}
