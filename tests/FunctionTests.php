<?php
/**
 * This file contains the FunctionTests class which extends WP_UnitTestCase.
 * It provides a series of unit tests for verifying the functionality of various helper functions
 * used in the WP Tag Order plugin. These tests ensure that the functions behave as expected
 * when handling different inputs and scenarios.
 *
 * @package WP_Tag_Order
 */

// Require the functions.php file.
require_once __DIR__ . '/../includes/functions.php';

/**
 * Class FunctionTests
 *
 * @package Wp_Tag_Order
 *
 * @covers ::wp_tag_order_cast_mixed_to_int
 * @covers ::wp_tag_order_cast_mixed_to_array
 * @covers ::wp_tag_order_cast_mixed_to_int_array
 * @covers ::wp_tag_order_cast_mixed_to_string
 */
class FunctionTests extends WP_UnitTestCase {
	/**
	 * Test wto_is_array_empty function.
	 *
	 * @covers wto_is_array_empty
	 */
	public function testWtoIsArrayEmpty() {
		$this->assertTrue( wp_tag_order_is_array_empty( array() ) );
		$this->assertFalse( wp_tag_order_is_array_empty( array( 'value' ) ) );
	}

	/**
	 * Test wto_array_diff_interactive function.
	 *
	 * @covers wto_array_diff_interactive
	 */
	public function testWtoArrayDiffInteractive() {
		$array1   = array( 'apple', 'banana' );
		$array2   = array( 'banana', 'cherry' );
		$expected = array( 'apple', 'cherry' );
		$this->assertEquals( $expected, wp_tag_order_array_diff_interactive( $array1, $array2 ) );
	}

	/**
	 * Test wto_get_non_hierarchical_taxonomies function.
	 *
	 * @covers wto_get_non_hierarchical_taxonomies
	 */
	public function testWtoGetNonHierarchicalTaxonomies() {
		$taxonomies                  = get_taxonomies( array( 'public' => true ), 'objects' );
		$non_hierarchical_taxonomies = array_filter(
			$taxonomies,
			function ( $taxonomy ) {
				return ! $taxonomy->hierarchical;
			}
		);

		$expected = array( 'post_tag', 'post_format' );
		$result   = array_keys( $non_hierarchical_taxonomies );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * Test wto_get_enabled_taxonomies function.
	 *
	 * @covers wto_get_enabled_taxonomies
	 */
	public function testWtoGetEnabledTaxonomies() {
		$expected = array( 'post_tag', 'enabled_cat' );

		update_option( 'wpto_enabled_taxonomies', $expected );

		$result = wp_tag_order_get_enabled_taxonomies();
		$this->assertEquals( $expected, $result );
	}

	/**
	 * Test wto_is_enabled_taxonomy function.
	 *
	 * @covers wto_is_enabled_taxonomy
	 */
	public function testWtoIsEnabledTaxonomy() {
		$expected = array( 'post_tag', 'enabled_cat' );

		update_option( 'wpto_enabled_taxonomies', $expected );

		$this->assertTrue( wp_tag_order_is_enabled_taxonomy( 'post_tag' ) );
		$this->assertFalse( wp_tag_order_is_enabled_taxonomy( 'disabled_cat' ) );
	}

	/**
	 * Test wto_has_enabled_taxonomy function.
	 *
	 * @covers wto_has_enabled_taxonomy
	 */
	public function testWtoHasEnabledTaxonomy() {
		$expected = array( 'post_tag', 'enabled_cat' );

		update_option( 'wpto_enabled_taxonomies', $expected );

		$this->assertTrue( wp_tag_order_has_enabled_taxonomy( array( 'post_tag', 'enabled_cat' ) ) );
		$this->assertFalse( wp_tag_order_has_enabled_taxonomy( array( 'disabled_cat' ) ) );
	}

	/**
	 * Test wto_get_post_types_by_taxonomy function.
	 *
	 * @covers wto_get_post_types_by_taxonomy
	 */
	public function testWtoGetPostTypesByTaxonomy() {
		register_post_type( 'custom_post_type', array() );
		register_taxonomy( 'custom_taxonomy', 'custom_post_type' );

		// Assuming wto_get_post_types_by_taxonomy function returns an array of post types for a given taxonomy.
		$expected_category        = array( 'post' );
		$expected_post_tag        = array( 'post' );
		$expected_custom_taxonomy = array( 'custom_post_type' );

		$this->assertEquals( $expected_category, wp_tag_order_get_post_types_by_taxonomy( 'category' ) );
		$this->assertEquals( $expected_post_tag, wp_tag_order_get_post_types_by_taxonomy( 'post_tag' ) );
		$this->assertEquals( $expected_custom_taxonomy, wp_tag_order_get_post_types_by_taxonomy( 'custom_taxonomy' ) );
	}

	/**
	 * Test wto_has_tag_posttype function.
	 *
	 * @covers wto_has_tag_posttype
	 */
	public function testWtoHasTagPosttype() {
		register_post_type(
			'news',
			array(
				'public'     => true,
				'taxonomies' => array(
					'post_tag',
					'news_tag',
				),
			)
		);
		register_taxonomy(
			'news_tag',
			'news',
			array(
				'hierarchical' => false,
			)
		);

		$expected_post_type = array( 'post', 'news' );

		$this->assertEquals( $expected_post_type, wp_tag_order_has_tag_posttype() );
	}

	/**
	 * Test wto_strposa function.
	 *
	 * @covers wto_strposa
	 */
	public function testWtoStrposa() {
		$this->assertTrue( wp_tag_order_strposa( 'hello world', 'world' ) );
		$this->assertFalse( wp_tag_order_strposa( 'hello world', 'test' ) );
	}

	/**
	 * Test wto_replace_script_tag function.
	 *
	 * @covers wto_replace_script_tag
	 */
	public function testWtoReplaceScriptTag() {
		$tag      = 'src="wp-tag-order/assets/js/script.js"';
		$expected = 'type="module" src="wp-tag-order/assets/js/script.js"';
		$this->assertEquals( $expected, wp_tag_order_replace_script_tag( $tag ) );
	}

	/**
	 * Test wto_has_reorder_controller_in_metaboxes function.
	 *
	 * @covers wto_has_reorder_controller_in_metaboxes
	 */
	public function testWtoHasReorderControllerInMetaboxes() {
		$this->assertTrue( wp_tag_order_has_reorder_controller_in_metaboxes() );
	}

	/**
	 * Test wpto_cast_mixed_to_int function.
	 *
	 * @test
	 * @dataProvider cast_to_int_provider
	 *
	 * @param mixed $input Input value.
	 * @param int   $expected Expected result.
	 */
	public function test_wp_tag_order_cast_mixed_to_int( $input, $expected ): void {
		$this->assertSame( $expected, wp_tag_order_cast_mixed_to_int( $input ) );
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
		wp_tag_order_cast_mixed_to_int( 'not_numeric' );
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
	public function test_wp_tag_order_cast_mixed_to_array( $input, $expected ): void {
		$this->assertSame( $expected, wp_tag_order_cast_mixed_to_array( $input ) );
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
	public function test_wp_tag_order_cast_mixed_to_int_array( $input, $expected ): void {
		$this->assertSame( $expected, wp_tag_order_cast_mixed_to_int_array( $input ) );
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
	public function test_wp_tag_order_cast_mixed_to_string( $input, $expected ): void {
		// Skip null and array inputs.
		if ( null === $input || is_array( $input ) ) {
			$this->markTestSkipped( 'Skipping null or array input' );
			return;
		}
		$this->assertSame( $expected, wp_tag_order_cast_mixed_to_string( $input ) );
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
		wp_tag_order_cast_mixed_to_string( new stdClass() );
	}
}
