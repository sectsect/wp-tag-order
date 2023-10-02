<?php
/**
 * General functions.
 *
 * @link       https://www.ilovesect.com/
 * @since      1.0.0
 *
 * @package    WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 */

/**
 * Template for catogory.
 *
 * @package    WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 */

/**
 * Detect empty array.
 *
 * @param  array $array "description".
 *
 * @return boolean "description".
 */
function wto_is_array_empty( $array ) {
	$array = array_filter( (array) $array );
	if ( empty( $array ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Diff the arrays.
 *
 * @param  array $array_1 "description".
 * @param  array $array_2 "description".
 *
 * @return array "description".
 */
function array_diff_interactive( $array_1, $array_2 ) {
	$compare_1_to_2   = array_diff( $array_1, $array_2 );
	$compare_2_to_1   = array_diff( $array_2, $array_1 );
	$difference_array = array_merge( $compare_1_to_2, $compare_2_to_1 );

	return $difference_array;
}

/**
 * Get all non-hierarchical taxonomies in WordPress, excluding the built-in `post_format` taxonomy.
 *
 * @return array array of non-hierarchical taxonomies.
 */
function wto_get_non_hierarchical_taxonomies() {
	$args               = array(
		'object_type'  => array( 'post' ),
		'public'       => true,
		'_builtin'     => true,
		'hierarchical' => false,
	);
	$output             = 'objects';
	$operator           = 'and';
	$taxonomies_builtin = get_taxonomies( $args, $output, $operator );
	// Drop post_format taxonomy from the array.
	$taxonomies_builtin = array_filter(
		$taxonomies_builtin,
		function( $taxonomies_builtin ) {
			return $taxonomies_builtin->name !== 'post_format';
		}
	);

	$args              = array(
		'public'       => true,
		'_builtin'     => false,
		'hierarchical' => false,
	);
	$output            = 'objects';
	$operator          = 'and';
	$taxonomies_custom = get_taxonomies( $args, $output, $operator );

	$taxonomies = array_merge( $taxonomies_builtin, $taxonomies_custom );

	return $taxonomies;
}

/**
 * Get the value of the "wpto_enabled_taxonomies" option.
 *
 * @return string the value of the option 'wpto_enabled_taxonomies'.
 */
function wto_get_enabled_taxonomies() {
	return get_option( 'wpto_enabled_taxonomies' );
}

/**
 * Checks if a given taxonomy is enabled taxonomy or not.
 *
 * @param taxonomy The parameter "taxonomy" is a string that represents the name of a taxonomy.
 *
 * @return boolean boolean value. It returns true if the given taxonomy is enabled, and false otherwise.
 */
function wto_is_enabled_taxonomy( $taxonomy ) {
	$enabled_taxonomies = wto_get_enabled_taxonomies();
	if ( ! empty( $enabled_taxonomies ) && in_array( $taxonomy, $enabled_taxonomies, true ) ) {
		return true;
	}
	return false;
}

/**
 * checks if any of the elements in the "needles" array are present in the "haystack" array.
 *
 * @param needles An array of values that we want to check if they exist in the haystack array.
 * @param haystack The haystack parameter is an array in which we want to search for the needles.
 *
 * @see https://stackoverflow.com/a/11040612/4542456
 *
 * @return boolean boolean value. It will return true if there is any intersection between the  array
 * and the  array, and false otherwise.
 */
function wto_in_array_any( $needles, $haystack ) {
	return ! empty( array_intersect( $needles, $haystack ) );
}

/**
 * Cchecks if any of the given taxonomies are enabled.
 *
 * @param taxonomies An array of taxonomy names.
 *
 * @return array result of the wto_in_array_any() function, which checks if any of the taxonomies in the
 *  array are present in the  array.
 */
function wto_has_enabled_taxonomy( $taxonomies ) {
	$taxonomies_enabled = wto_get_enabled_taxonomies();
	return wto_in_array_any( $taxonomies_enabled, $taxonomies );
}

function wto_get_post_types_by_taxonomy( $tax = 'category' ) {
	global $wp_taxonomies;
	return ( isset( $wp_taxonomies[ $tax ] ) ) ? $wp_taxonomies[ $tax ]->object_type : array();
}

/**
 * Get All Post-types that holds the non-hierarchical taxonomies.
 *
 * @return array "description".
 */
function wto_has_tag_posttype() {
	$args      = array(
		'public'   => true,
		'_builtin' => false,
	);
	$output    = 'names';
	$operator  = 'and';
	$default   = array( 'post' => 'post' );
	$cpt       = get_post_types( $args, $output, $operator );
	$posttypes = array_merge( $default, $cpt );

	$hastagposttypes = array();
	foreach ( $posttypes as $posttype ) {
		$taxonomies = get_object_taxonomies( $posttype );
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				// only want hierarchical -- no tags please !
				if ( ! is_taxonomy_hierarchical( $taxonomy ) ) {
					array_push( $hastagposttypes, $posttype );
				}
			}
		}
	}
	$hastagposttypes = array_unique( $hastagposttypes );

	return $hastagposttypes;
}

/**
 * Using an array as needles in strpos for replace_script_tag().
 *
 * @param  mixed $haystack
 * @param  mixed $needle
 * @param  mixed $offset
 * @return false
 */
function wto_strposa( $haystack, $needle, $offset = 0 ) {
	if ( ! is_array( $needle ) ) {
		$needle = array( $needle );
	}
	foreach ( $needle as $query ) {
		if ( strpos( $haystack, $query, $offset ) !== false ) {
			return true; // stop on first true result
		}
	}
	return false;
}

/**
 * Add `type="module"` to <script> on wp_enqueue_script().
 *
 * @param  mixed $tag
 * @return $tag
 */
function wto_replace_script_tag( $tag ) {
	$module = array( 'wp-tag-order/assets/js/' );
	if ( wto_strposa( $tag, $module ) ) {
		$tag = str_replace( " type='text/javascript'", '', $tag );
		$tag = str_replace( '<script src=', '<script type="module" src=', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'wto_replace_script_tag', 10, 1 );

/**
 * Checks if the WordPress version is 5.5 or higher.
 *
 * @see https://make.wordpress.org/core/2020/08/06/allow-post-boxes-and-metaboxes-to-be-reordered-by-using-the-keyboard/
 * @return boolean result of the comparison between the WordPress version and '5.5'.
 */
function wto_has_reorder_controller_in_metaboxes() {
	return version_compare( get_bloginfo( 'version' ), '5.5', '>=' );
}
