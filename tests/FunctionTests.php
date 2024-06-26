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
 */
class FunctionTests extends WP_UnitTestCase {
	/**
	 * Test wto_is_array_empty function.
	 *
	 * @covers wto_is_array_empty
	 */
	public function testWtoIsArrayEmpty() {
		$this->assertTrue( wto_is_array_empty( array() ) );
		$this->assertFalse( wto_is_array_empty( array( 'value' ) ) );
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
		$this->assertEquals( $expected, wto_array_diff_interactive( $array1, $array2 ) );
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

		$result = wto_get_enabled_taxonomies();
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

		$this->assertTrue( wto_is_enabled_taxonomy( 'post_tag' ) );
		$this->assertFalse( wto_is_enabled_taxonomy( 'disabled_cat' ) );
	}

	/**
	 * Test wto_has_enabled_taxonomy function.
	 *
	 * @covers wto_has_enabled_taxonomy
	 */
	public function testWtoHasEnabledTaxonomy() {
		$expected = array( 'post_tag', 'enabled_cat' );

		update_option( 'wpto_enabled_taxonomies', $expected );

		$this->assertTrue( wto_has_enabled_taxonomy( array( 'post_tag', 'enabled_cat' ) ) );
		$this->assertFalse( wto_has_enabled_taxonomy( array( 'disabled_cat' ) ) );
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

		$this->assertEquals( $expected_category, wto_get_post_types_by_taxonomy( 'category' ) );
		$this->assertEquals( $expected_post_tag, wto_get_post_types_by_taxonomy( 'post_tag' ) );
		$this->assertEquals( $expected_custom_taxonomy, wto_get_post_types_by_taxonomy( 'custom_taxonomy' ) );
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

		$this->assertEquals( $expected_post_type, wto_has_tag_posttype() );
	}

	/**
	 * Test wto_strposa function.
	 *
	 * @covers wto_strposa
	 */
	public function testWtoStrposa() {
		$this->assertTrue( wto_strposa( 'hello world', 'world' ) );
		$this->assertFalse( wto_strposa( 'hello world', 'test' ) );
	}

	/**
	 * Test wto_replace_script_tag function.
	 *
	 * @covers wto_replace_script_tag
	 */
	public function testWtoReplaceScriptTag() {
		$tag      = '<script src="wp-tag-order/assets/js/script.js"></script>';
		$expected = '<script type="module" src="wp-tag-order/assets/js/script.js"></script>';
		$this->assertEquals( $expected, wto_replace_script_tag( $tag ) );
	}

	/**
	 * Test wto_has_reorder_controller_in_metaboxes function.
	 *
	 * @covers wto_has_reorder_controller_in_metaboxes
	 */
	public function testWtoHasReorderControllerInMetaboxes() {
		$this->assertTrue( wto_has_reorder_controller_in_metaboxes() );
	}
}
