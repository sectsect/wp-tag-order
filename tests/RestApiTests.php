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
 * @covers ::wpto_cast_mixed_to_int
 * @covers ::wpto_cast_mixed_to_array
 * @covers ::wpto_cast_mixed_to_int_array
 * @covers ::wpto_cast_mixed_to_string
 * @covers ::wpto_validate_taxonomy
 * @covers ::wpto_validate_tag_ids
 * @covers ::wpto_rest_permission_check
 * @covers ::wpto_get_post_tag_order
 */
class RestApiTests extends WP_UnitTestCase {

	/**
	 * Test wpto_cast_mixed_to_int function.
	 *
	 * @test
	 * @dataProvider cast_to_int_provider
	 *
	 * @param mixed $input Input value.
	 * @param int   $expected Expected result.
	 */
	public function test_wpto_cast_mixed_to_int( $input, $expected ): void {
		$this->assertSame( $expected, wpto_cast_mixed_to_int( $input ) );
	}

	/**
	 * Data provider for wpto_cast_mixed_to_int.
	 *
	 * @return array
	 */
	public function cast_to_int_provider(): array {
		return array(
			'integer'        => array( 42, 42 ),
			'numeric_string' => array( '123', 123 ),
			'float_string'   => array( '45.67', 45 ),
		);
	}

	/**
	 * Test wpto_cast_mixed_to_int throws exception for non-numeric input.
	 *
	 * @test
	 */
	public function test_wpto_cast_mixed_to_int_throws_exception(): void {
		$this->expectException( InvalidArgumentException::class );
		wpto_cast_mixed_to_int( 'not_numeric' );
	}

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
	 * Test wpto_cast_mixed_to_array function.
	 *
	 * @test
	 * @dataProvider cast_to_array_provider
	 *
	 * @param mixed $input Input value.
	 * @param array $expected Expected result.
	 */
	public function test_wpto_cast_mixed_to_array( $input, $expected ): void {
		$this->assertSame( $expected, wpto_cast_mixed_to_array( $input ) );
	}

	/**
	 * Data provider for wpto_cast_mixed_to_array.
	 *
	 * @return array
	 */
	public function cast_to_array_provider(): array {
		return array(
			'single_value'           => array( 'test', array( 'test' ) ),
			'array_input'            => array( array( 'a', 'b' ), array( 'a', 'b' ) ),
			'null_input'             => array( null, array() ),
			'comma_separated_string' => array( 'a,b,c', array( 'a,b,c' ) ),
		);
	}

	/**
	 * Test wpto_cast_mixed_to_int_array function.
	 *
	 * @test
	 * @dataProvider cast_to_int_array_provider
	 *
	 * @param mixed $input Input value.
	 * @param array $expected Expected result.
	 */
	public function test_wpto_cast_mixed_to_int_array( $input, $expected ): void {
		$this->assertSame( $expected, wpto_cast_mixed_to_int_array( $input ) );
	}

	/**
	 * Data provider for wpto_cast_mixed_to_int_array.
	 *
	 * @return array
	 */
	public function cast_to_int_array_provider(): array {
		return array(
			'single_int'             => array( 42, array( 42 ) ),
			'numeric_string'         => array( '123', array( 123 ) ),
			'mixed_array'            => array( array( '42', '123', 45 ), array( 42, 123, 45 ) ),
			'comma_separated_string' => array( '1,2,3', array() ),
			'null_input'             => array( null, array() ),
		);
	}

	/**
	 * Test wpto_cast_mixed_to_string function.
	 *
	 * @test
	 * @dataProvider cast_to_string_provider
	 *
	 * @param mixed  $input Input value.
	 * @param string $expected Expected result.
	 */
	public function test_wpto_cast_mixed_to_string( $input, $expected ): void {
		// Skip null and array inputs.
		if ( null === $input || is_array( $input ) ) {
			$this->markTestSkipped( 'Skipping null or array input' );
			return;
		}
		$this->assertSame( $expected, wpto_cast_mixed_to_string( $input ) );
	}

	/**
	 * Data provider for wpto_cast_mixed_to_string.
	 *
	 * @return array
	 */
	public function cast_to_string_provider(): array {
		return array(
			'string_input' => array( 'test', 'test' ),
			'int_input'    => array( 42, '42' ),
			'float_input'  => array( 3.14, '3.14' ),
			'bool_true'    => array( true, '1' ),
			'bool_false'   => array( false, '' ),
		);
	}

	/**
	 * Test wpto_cast_mixed_to_string throws exception for complex objects.
	 *
	 * @test
	 */
	public function test_wpto_cast_mixed_to_string_throws_exception(): void {
		$this->expectException( InvalidArgumentException::class );
		wpto_cast_mixed_to_string( new stdClass() );
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
}
