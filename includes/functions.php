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
