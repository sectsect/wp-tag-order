<?php
/**
 * General functions.
 *
 * @since 1.0.0
 *
 * @package WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 */

/**
 * Checks if an array is empty.
 *
 * @param array $arr The array to check.
 *
 * @return bool True if the array is empty, false otherwise.
 */
function wto_is_array_empty( $arr ) {
	return empty( array_filter( (array) $arr ) );
}

/**
 * Computes the difference between two arrays.
 *
 * @param array $array_1 The first array.
 * @param array $array_2 The second array.
 *
 * @return array The difference between the two arrays.
 */
function wto_array_diff_interactive( $array_1, $array_2 ) {
	return array_merge( array_diff( $array_1, $array_2 ), array_diff( $array_2, $array_1 ) );
}

/**
 * Retrieves all non-hierarchical taxonomies in WordPress, excluding the built-in `post_format` taxonomy.
 *
 * @return array An array of non-hierarchical taxonomies.
 */
function wto_get_non_hierarchical_taxonomies() {
	$args               = array(
		'object_type'  => array( 'post' ),
		'public'       => true,
		'_builtin'     => true,
		'hierarchical' => false,
	);
	$taxonomies_builtin = get_taxonomies( $args, 'objects', 'and' );
	// Drop post_format taxonomy from the array.
	$taxonomies_builtin = array_filter(
		$taxonomies_builtin,
		function ( $taxonomies_builtin ) {
			return 'post_format' !== $taxonomies_builtin->name;
		}
	);

	$args              = array(
		'public'       => true,
		'_builtin'     => false,
		'hierarchical' => false,
	);
	$taxonomies_custom = get_taxonomies( $args, 'objects', 'and' );

	return array_merge( $taxonomies_builtin, $taxonomies_custom );
}

/**
 * Retrieves the value of the "wpto_enabled_taxonomies" option.
 *
 * @return array The value of the "wpto_enabled_taxonomies" option.
 */
function wto_get_enabled_taxonomies() {
	return get_option( 'wpto_enabled_taxonomies', array() );
}

/**
 * Checks if a given taxonomy is enabled.
 *
 * @param string $taxonomy The taxonomy to check.
 *
 * @return bool True if the taxonomy is enabled, false otherwise.
 */
function wto_is_enabled_taxonomy( $taxonomy ) {
	return in_array( $taxonomy, wto_get_enabled_taxonomies(), true );
}

/**
 * Checks if any of the given taxonomies are enabled.
 *
 * @param array $taxonomies The taxonomies to check.
 *
 * @return bool True if any of the taxonomies are enabled, false otherwise.
 *
 * @see https://stackoverflow.com/a/11040612/4542456
 */
function wto_has_enabled_taxonomy( $taxonomies ) {
	return ! empty( array_intersect( wto_get_enabled_taxonomies(), $taxonomies ) );
}

/**
 * Returns an array of post types associated with a given taxonomy.
 *
 * @param string $tax The taxonomy slug. Default is 'category'.
 *
 * @return array An array of post types associated with the given taxonomy.
 */
function wto_get_post_types_by_taxonomy( $tax = 'category' ) {
	global $wp_taxonomies;
	return $wp_taxonomies[ $tax ]->object_type ?? array();
}

/**
 * Retrieves all post types that have non-hierarchical taxonomies.
 *
 * @return array An array of post types that have non-hierarchical taxonomies.
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
		foreach ( $taxonomies as $taxonomy ) {
			if ( ! is_taxonomy_hierarchical( $taxonomy ) ) {
				$hastagposttypes[] = $posttype;
			}
		}
	}
	return array_unique( $hastagposttypes );
}

/**
 * Checks if any of the needles are found in the haystack string.
 *
 * @param string       $haystack The string to search in.
 * @param string|array $needles  The string or array of strings to search for.
 * @param int          $offset   The starting position for the search. Default is 0.
 *
 * @return bool True if any of the needles are found in the haystack, false otherwise.
 */
function wto_strposa( $haystack, $needles, $offset = 0 ) {
	$needles = (array) $needles;
	foreach ( $needles as $needle ) {
		if ( strpos( $haystack, $needle, $offset ) !== false ) {
			return true;
		}
	}
	return false;
}

/**
 * Adds the `type="module"` attribute to script tags for specific scripts.
 *
 * @param string $tag The HTML script tag.
 *
 * @return string The modified HTML script tag.
 */
function wto_replace_script_tag( $tag ) {
	$module = array( 'wp-tag-order/assets/js/' );
	if ( wto_strposa( $tag, $module ) ) {
		$tag = str_replace( array( " type='text/javascript'", '<script src=' ), array( '', '<script type="module" src=' ), $tag );
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
