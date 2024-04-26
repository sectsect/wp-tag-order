<?php

// // Mocking WordPress function add_filter if not exists
// function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
// 	// Mock implementation can be customized as needed
// }

// Mocking WordPress function get_option if not exists
function get_option( $option_name ) {
	$options = array(
		'wpto_enabled_taxonomies' => array( 'post_tag', 'enabled_cat' ),
	);

	return $options[ $option_name ] ?? false;
}

// Mocking WordPress function get_taxonomies if not exists
function get_taxonomies( $args = array(), $output = 'names', $operator = 'and' ) {
	return array(
		'post_tag'    => (object) array( 'hierarchical' => false ),
		'category'    => (object) array( 'hierarchical' => true ),
		'post_format' => (object) array( 'hierarchical' => false ),
	);
}

// Mocking WordPress global variable $wp_taxonomies if not exists
$GLOBALS['wp_taxonomies'] = array(
	'category'        => (object) array(
		'name'         => 'category',
		'object_type'  => array( 'post' ),
		'hierarchical' => true,
	),
	'post_tag'        => (object) array(
		'name'         => 'post_tag',
		'object_type'  => array( 'post' ),
		'hierarchical' => false,
	),
	'custom_taxonomy' => (object) array(
		'name'         => 'custom_taxonomy',
		'object_type'  => array( 'custom_post_type' ),
		'hierarchical' => false,
	),
);

// Mocking WordPress function get_post_types if not exists
function get_post_types( $args = array(), $output = 'names', $operator = 'and' ) {
	return array(
		'post'             => 'post',
		'news'             => 'news',
		'custom_post_type' => 'custom_post_type',
	)[ $output ];
}

// Mocking WordPress function get_object_taxonomies if not exists
function get_object_taxonomies( $object, $output = 'names' ) {
	$taxonomies = array(
		'post'             => array( 'category', 'post_tag' ),
		'news'             => array( 'page_tag' ),
		'custom_post_type' => array( 'custom_taxonomy' ),
	);

	return $taxonomies[ $object ] ?? array();
}

// Mocking WordPress function get_bloginfo if not exists
function get_bloginfo( $show = '' ) {
	$info = array(
		'name'           => 'Test Site',
		'description'    => 'Just another WordPress site',
		'wpurl'          => 'http://localhost',
		'url'            => 'http://localhost',
		'admin_email'    => 'admin@example.com',
		'charset'        => 'UTF-8',
		'version'        => '5.8',
		'html_type'      => 'text/html',
		'text_direction' => 'ltr',
		'language'       => 'en-US',
	);

	return $info[ $show ] ?? '';
}

// Mocking WordPress function is_taxonomy_hierarchical() if not exists
function is_taxonomy_hierarchical( $taxonomy ) {
	return true;
}

require_once __DIR__ . '/../includes/functions.php';

class FunctionTests extends WP_UnitTestCase {
	/**
	 * @covers wto_is_array_empty
	 */
	public function testWtoIsArrayEmpty() {
		$this->assertTrue( wto_is_array_empty( array() ) );
		$this->assertFalse( wto_is_array_empty( array( 'value' ) ) );
	}

	/**
	 * @covers wto_array_diff_interactive
	 */
	public function testWtoArrayDiffInteractive() {
		$array1   = array( 'apple', 'banana' );
		$array2   = array( 'banana', 'cherry' );
		$expected = array( 'apple', 'cherry' );
		$this->assertEquals( $expected, wto_array_diff_interactive( $array1, $array2 ) );
	}

	/**
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
	 * @covers wto_get_enabled_taxonomies
	 */
	public function testWtoGetEnabledTaxonomies() {
		$expected = array( 'post_tag', 'enabled_cat' );

		$result = wto_get_enabled_taxonomies();
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers wto_is_enabled_taxonomy
	 */
	public function testWtoIsEnabledTaxonomy() {
		$this->assertTrue( wto_is_enabled_taxonomy( 'post_tag' ) );
		$this->assertFalse( wto_is_enabled_taxonomy( 'disabled_cat' ) );
	}

	/**
	 * @covers wto_has_enabled_taxonomy
	 */
	public function testWtoHasEnabledTaxonomy() {
		$this->assertTrue( wto_has_enabled_taxonomy( array( 'post_tag', 'enabled_cat' ) ) );
		$this->assertFalse( wto_has_enabled_taxonomy( array( 'disabled_cat' ) ) );
	}

	/**
	 * @covers wto_get_post_types_by_taxonomy
	 */
	public function testWtoGetPostTypesByTaxonomy() {
		// Assuming wto_get_post_types_by_taxonomy function returns an array of post types for a given taxonomy
		$expected_category        = array( 'post' );
		$expected_post_tag        = array( 'post' );
		$expected_custom_taxonomy = array( 'custom_post_type' );

		$this->assertEquals( $expected_category, wto_get_post_types_by_taxonomy( 'category' ) );
		$this->assertEquals( $expected_post_tag, wto_get_post_types_by_taxonomy( 'post_tag' ) );
		$this->assertEquals( $expected_custom_taxonomy, wto_get_post_types_by_taxonomy( 'custom_taxonomy' ) );
	}

	/**
	 * @covers wto_has_tag_posttype
	 */
	// public function testWtoHasTagPosttype() {
	// 	$expected_post_type = array( 'post', 'news' );

	// 	$this->assertEquals( $expected_post_type, wto_has_tag_posttype() );
	// }

	/**
	 * @covers wto_strposa
	 */
	public function testWtoStrposa() {
		$this->assertTrue( wto_strposa( 'hello world', 'world' ) );
		$this->assertFalse( wto_strposa( 'hello world', 'test' ) );
	}

	/**
	 * @covers wto_replace_script_tag
	 */
	public function testWtoReplaceScriptTag() {
		$tag      = '<script src="wp-tag-order/assets/js/script.js"></script>';
		$expected = '<script type="module" src="wp-tag-order/assets/js/script.js"></script>';
		$this->assertEquals( $expected, wto_replace_script_tag( $tag ) );
	}

	/**
	 * @covers wto_has_reorder_controller_in_metaboxes
	 */
	public function testWtoHasReorderControllerInMetaboxes() {
		$this->assertTrue( wto_has_reorder_controller_in_metaboxes() );
	}
}
