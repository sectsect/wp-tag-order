<?php
/**
 * General functions.
 *
 * @since 1.0.0
 *
 * @package WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 */

declare(strict_types=1);

/**
 * Checks if an array is empty.
 *
 * @param array<mixed> $arr The array to check.
 *
 * @return bool True if the array is empty, false otherwise.
 */
function wto_is_array_empty( array $arr ): bool {
	return empty( array_filter( (array) $arr ) );
}

/**
 * Computes the difference between two arrays.
 *
 * @param array<mixed> $array_1 The first array.
 * @param array<mixed> $array_2 The second array.
 *
 * @return array<mixed> The difference between the two arrays.
 */
function wto_array_diff_interactive( array $array_1, array $array_2 ): array {
	return array_merge( array_diff( $array_1, $array_2 ), array_diff( $array_2, $array_1 ) );
}

/**
 * Retrieves all non-hierarchical taxonomies in WordPress, excluding the built-in `post_format` taxonomy.
 *
 * @return array<string, WP_Taxonomy> An array of non-hierarchical taxonomies.
 */
function wto_get_non_hierarchical_taxonomies(): array {
	$args               = array(
		'object_type'  => array( 'post' ),
		'public'       => true,
		'_builtin'     => true,
		'hierarchical' => false,
	);
	$taxonomies_builtin = get_taxonomies( $args, 'objects', 'and' );
	// Drop `post_format` taxonomy from the array.
	$taxonomies_builtin = array_filter(
		$taxonomies_builtin,
		fn( $taxonomies_builtin ) => 'post_format' !== $taxonomies_builtin->name
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
 * @return array<string> The value of the "wpto_enabled_taxonomies" option.
 */
function wto_get_enabled_taxonomies(): array {
	$option = get_option( 'wpto_enabled_taxonomies', array() );
	return is_array( $option ) ? $option : array();
}

/**
 * Checks if a given taxonomy is enabled.
 *
 * @param string $taxonomy The taxonomy to check.
 *
 * @return bool True if the taxonomy is enabled, false otherwise.
 */
function wto_is_enabled_taxonomy( string $taxonomy ): bool {
	return in_array( $taxonomy, wto_get_enabled_taxonomies(), true );
}

/**
 * Checks if any of the given taxonomies are enabled.
 *
 * @param array<string> $taxonomies The taxonomies to check.
 *
 * @return bool True if any of the taxonomies are enabled, false otherwise.
 */
function wto_has_enabled_taxonomy( array $taxonomies ): bool {
	return ! empty( array_intersect( wto_get_enabled_taxonomies(), $taxonomies ) );
}

/**
 * Returns an array of post types associated with a given taxonomy.
 *
 * @param string $tax The taxonomy slug. Default is 'category'.
 *
 * @return array<string> An array of post types associated with the given taxonomy.
 */
function wto_get_post_types_by_taxonomy( string $tax = 'category' ): array {
	global $wp_taxonomies;
	return $wp_taxonomies[ $tax ]->object_type ?? array();
}

/**
 * Retrieves all post types that have non-hierarchical taxonomies.
 *
 * @return array<string> An array of post types that have non-hierarchical taxonomies.
 */
function wto_has_tag_posttype(): array {
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
	return array_values( array_unique( $hastagposttypes ) );
}

/**
 * Checks if any of the needles are found in the haystack string.
 *
 * @param string               $haystack The string to search in.
 * @param array<string>|string $needles  The string or array of strings to search for.
 * @param int                  $offset   The starting position for the search. Default is 0.
 *
 * @return bool True if any of the needles are found in the haystack, false otherwise.
 */
function wto_strposa( string $haystack, array|string $needles, int $offset = 0 ): bool {
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
function wto_replace_script_tag( string $tag ): string {
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
function wto_has_reorder_controller_in_metaboxes(): bool {
	return version_compare( get_bloginfo( 'version' ), '5.5', '>=' );
}

/**
 * Cast mixed value to int.
 *
 * @param mixed $value Value to cast.
 * @return int
 * @throws InvalidArgumentException If the value is not numeric.
 */
function wpto_cast_mixed_to_int( mixed $value ): int {
	if ( is_numeric( $value ) ) {
		return (int) $value;
	}
	throw new InvalidArgumentException( 'Value must be numeric' );
}

/**
 * Cast mixed value to array, returning an empty array if not an array.
 *
 * @param mixed $value Value to cast.
 * @return array<mixed>
 */
function wpto_cast_mixed_to_array( mixed $value ): array {
	if ( is_null( $value ) ) {
		return array();
	}

	return is_array( $value ) ? $value : array( $value );
}

/**
 * Cast mixed value to array of integers.
 *
 * @param mixed $value Value to cast.
 * @return array<int>
 */
function wpto_cast_mixed_to_int_array( mixed $value ): array {
	if ( is_null( $value ) ) {
		return array();
	}

	if ( ! is_array( $value ) ) {
		$value = array( $value );
	}

	return array_map( 'intval', array_filter( $value, 'is_numeric' ) );
}

/**
 * Cast mixed value to string.
 *
 * @param mixed $value Value to cast.
 * @return string
 * @throws InvalidArgumentException If the value cannot be cast to a string.
 */
function wpto_cast_mixed_to_string( mixed $value ): string {
	if ( is_null( $value ) ) {
		throw new InvalidArgumentException( 'Value cannot be null' );
	}

	if ( is_scalar( $value ) ) {
		return (string) $value;
	}

	throw new InvalidArgumentException( 'Value must be a scalar type' );
}

/**
 * Validate AJAX request with nonce verification and input sanitization
 *
 * @param string $action Nonce action name. Default is 'wpto_nonce_action'.
 * @return int Sanitized post ID
 */
function wpto_validate_ajax_request( string $action = 'wpto_nonce_action' ): int {
	// Validate request method.
	if ( ! isset( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( 'Invalid request method', 400 );
	}

	// Verify nonce.
	if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
		wp_send_json_error( 'Nonce verification failed', 403 );
	}

	// Sanitize and validate post ID.
	$post_id = isset( $_GET['post'] )
		? intval( sanitize_text_field( wp_unslash( $_GET['post'] ) ) )
		: 0;

	if ( $post_id <= 0 ) {
		wp_send_json_error( 'Invalid post ID', 400 );
	}

	return $post_id;
}
