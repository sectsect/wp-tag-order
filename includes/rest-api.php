<?php
/**
 * REST API Endpoints for WP Tag Order plugin.
 *
 * @package WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 * @since 3.9.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register REST API endpoints for tag ordering.
 *
 * @return void
 */
function wp_tag_order_register_rest_endpoints(): void {
	register_rest_route(
		WP_TAG_ORDER_REST_NAMESPACE,
		'/tags/order/(?P<post_id>\d+)',
		array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => 'wp_tag_order_get_post_tag_order',
			'permission_callback' => 'wp_tag_order_rest_permission_check',
			'args'                => array(
				'post_id' => array(
					'validate_callback' => function ( $value ) {
						return is_numeric( $value );
					},
					'required'          => true,
					'type'              => 'integer',
				),
			),
		)
	);

	register_rest_route(
		WP_TAG_ORDER_REST_NAMESPACE,
		'/tags/order/(?P<post_id>\d+)',
		array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => 'wp_tag_order_update_post_tag_order',
			'permission_callback' => 'wp_tag_order_rest_permission_check',
			'args'                => array(
				'post_id'  => array(
					'validate_callback' => function ( $value ) {
						return is_numeric( $value );
					},
					'required'          => true,
					'type'              => 'integer',
				),
				'taxonomy' => array(
					'required'          => true,
					'type'              => 'string',
					'validate_callback' => function ( $value, $request, $key ) {
						return wp_tag_order_validate_taxonomy( $value );
					},
				),
				'tags'     => array(
					'required'          => true,
					'type'              => 'string',
					'validate_callback' => function ( $value, $request, $key ) {
						$taxonomy = $request->get_param( 'taxonomy' );
						return wp_tag_order_validate_tag_ids( $value, $taxonomy );
					},
				),
			),
		)
	);

	register_rest_route(
		WP_TAG_ORDER_REST_NAMESPACE,
		'/taxonomies/enabled',
		array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => 'wp_tag_order_get_enabled_taxonomies_endpoint',
			'permission_callback' => 'wp_tag_order_rest_taxonomies_permission_check',
		)
	);
}
add_action( 'rest_api_init', 'wp_tag_order_register_rest_endpoints' );

/**
 * Validate taxonomy for REST API request.
 *
 * Performs comprehensive checks to ensure the taxonomy is valid:
 * - Checks if the taxonomy is enabled for the plugin
 * - Ensures the taxonomy is not hierarchical
 * - Verifies the taxonomy actually exists in WordPress
 * - Optionally checks for public visibility
 *
 * @param string $taxonomy Taxonomy name to validate.
 * @return bool True if taxonomy is valid, false otherwise.
 */
function wp_tag_order_validate_taxonomy( string $taxonomy ): bool {
	// Check if taxonomy exists in WordPress.
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return false;
	}

	// Check if taxonomy is enabled for the plugin.
	if ( ! wp_tag_order_is_enabled_taxonomy( $taxonomy ) ) {
		return false;
	}

	// Ensure taxonomy is not hierarchical.
	if ( is_taxonomy_hierarchical( $taxonomy ) ) {
		return false;
	}

	// Optional: Additional checks for taxonomy visibility.
	$taxonomy_object = get_taxonomy( $taxonomy );

	// Ensure taxonomy object is an instance of WP_Taxonomy.
	if ( ! $taxonomy_object instanceof WP_Taxonomy ) {
		return false;
	}

	// Ensure taxonomy is public or at least show_in_rest is true.
	if ( ! $taxonomy_object->public && ! $taxonomy_object->show_in_rest ) {
		return false;
	}

	return true;
}

/**
 * Validate tag IDs for REST API request.
 *
 * Performs comprehensive checks to ensure tag IDs are valid:
 * - Verifies all IDs are numeric
 * - Checks if tags exist in the specified taxonomy
 * - Ensures no duplicate tag IDs
 *
 * @param string $tags Comma-separated tag IDs.
 * @param string $taxonomy Taxonomy to validate tags against.
 * @return bool
 */
function wp_tag_order_validate_tag_ids( string $tags, string $taxonomy ): bool {
	// Split tags and convert to integers.
	$tag_array = array_map( 'intval', explode( ',', $tags ) );

	// Check for non-numeric values.
	$non_numeric_tags = array_filter(
		$tag_array,
		function ( $tag ) {
			return $tag <= 0;
		}
	);

	if ( ! empty( $non_numeric_tags ) ) {
		return false;
	}

	// Check for duplicate tag IDs.
	if ( count( $tag_array ) !== count( array_unique( $tag_array ) ) ) {
		return false;
	}

	// Validate each tag exists in the specified taxonomy.
	$invalid_tags = array_filter(
		$tag_array,
		function ( $tag_id ) use ( $taxonomy ) {
			// Check if the term exists in the specified taxonomy.
			$term = get_term_by( 'id', $tag_id, $taxonomy );
			return false === $term;
		}
	);

	// Return true only if no invalid tags are found.
	return empty( $invalid_tags );
}

/**
 * Permission check for REST API endpoints.
 *
 * @param \WP_REST_Request $request REST request object.
 * @return bool
 *
 * @phpstan-param WP_REST_Request<array{post_id?: int}> $request
 */
function wp_tag_order_rest_permission_check( \WP_REST_Request $request ): bool {
	// Always allow GET requests.
	if ( 'GET' === $request->get_method() ) {
		return true;
	}

	// Only allow logged-in users.
	if ( ! is_user_logged_in() ) {
		return false;
	}

	// Validate and retrieve post ID.
	try {
		$post_id = wp_tag_order_cast_mixed_to_int( $request->get_param( 'post_id' ) );
	} catch ( InvalidArgumentException $e ) {
		return false;
	}

	// Check if post exists.
	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}

	// Restrict to allowed post types.
	$allowed_post_types = apply_filters( 'wp_tag_order_allowed_post_types', array( 'post', 'page' ) );
	if ( ! in_array( $post->post_type, $allowed_post_types, true ) ) {
		return false;
	}

	// Check user permissions.
	return current_user_can( 'edit_post', $post_id ) ||
			get_current_user_id() === (int) $post->post_author;
}

/**
 * Get tag order for a specific post.
 *
 * Retrieves the ordered tags for a given post and taxonomy.
 * Returns detailed tag information including term_id, name, slug, etc.
 *
 * @param \WP_REST_Request $request REST request object.
 * @return \WP_REST_Response
 *
 * @phpstan-param WP_REST_Request<array{post_id?: int, taxonomy?: string}> $request
 */
function wp_tag_order_get_post_tag_order( \WP_REST_Request $request ): \WP_REST_Response {
	$post_id      = wp_tag_order_cast_mixed_to_int( $request->get_param( 'post_id' ) );
	$taxonomy     = $request->get_param( 'taxonomy' ) ?? 'post_tag';
	$tags_value   = get_post_meta( $post_id, wp_tag_order_meta_key( $taxonomy ), true );
	$tags         = is_string( $tags_value ) ? unserialize( $tags_value ) : array();
	$ordered_tags = array_map(
		function ( $tag_id ) use ( $taxonomy ): ?array {
			if ( ! is_int( $tag_id ) ) {
				return null;
			}
			$tag = get_term_by( 'id', $tag_id, $taxonomy );
			if ( ! $tag instanceof \WP_Term ) {
				return null;
			}
			return array(
				'term_id'          => $tag->term_id,
				'name'             => $tag->name,
				'slug'             => $tag->slug,
				'term_group'       => $tag->term_group,
				'term_taxonomy_id' => $tag->term_taxonomy_id,
				'taxonomy'         => $tag->taxonomy,
				'description'      => $tag->description,
				'parent'           => $tag->parent,
				'count'            => $tag->count,
			);
		},
		is_array( $tags ) ? $tags : array()
	);

	$ordered_tags = array_filter( $ordered_tags );

	return rest_ensure_response( $ordered_tags );
}

/**
 * Update tag order for a specific post.
 *
 * Handles the process of updating tag order for a given post and taxonomy.
 * Performs comprehensive validation and provides detailed error responses.
 *
 * @param \WP_REST_Request $request REST request object.
 * @return \WP_REST_Response
 *
 * @phpstan-param WP_REST_Request<array{post_id?: int, taxonomy?: string, tags?: string}> $request
 */
function wp_tag_order_update_post_tag_order( \WP_REST_Request $request ): \WP_REST_Response {
	try {
		// Cast and validate input parameters.
		$post_id  = wp_tag_order_cast_mixed_to_int( $request->get_param( 'post_id' ) );
		$taxonomy = wp_tag_order_cast_mixed_to_string( $request->get_param( 'taxonomy' ) );
		$tags     = explode( ',', wp_tag_order_cast_mixed_to_string( $request->get_param( 'tags' ) ) );
		$tags     = wp_tag_order_cast_mixed_to_int_array( $tags );

		// Validate taxonomy.
		if ( ! wp_tag_order_validate_taxonomy( $taxonomy ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'code'    => 'invalid_taxonomy',
					'message' => __( 'Invalid or unsupported taxonomy.', 'wp-tag-order' ),
					'data'    => array(
						'status'   => 400,
						'taxonomy' => $taxonomy,
					),
				)
			);
		}

		// Validate tag IDs exist in the specified taxonomy.
		$invalid_tags = array_filter(
			$tags,
			function ( $tag_id ) use ( $taxonomy ) {
				return ! term_exists( $tag_id, $taxonomy );
			}
		);

		if ( ! empty( $invalid_tags ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'code'    => 'invalid_tags',
					'message' => __( 'One or more tag IDs are invalid.', 'wp-tag-order' ),
					'data'    => array(
						'status'       => 400,
						'invalid_tags' => $invalid_tags,
						'taxonomy'     => $taxonomy,
					),
				)
			);
		}

		// Retrieve current tag order.
		$current_tags_value = get_post_meta( $post_id, wp_tag_order_meta_key( $taxonomy ), true );
		$current_tags       = wp_tag_order_cast_mixed_to_array(
			is_string( $current_tags_value ) ? unserialize( $current_tags_value ) : $current_tags_value
		);

		// Check if tag order remains unchanged.
		$tags_unchanged = (
			count( $current_tags ) === count( $tags ) &&
			$current_tags === $tags
		);

		if ( $tags_unchanged ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'code'    => 'no_changes',
					'message' => __( 'No changes in tag order detected.', 'wp-tag-order' ),
				)
			);
		}

		// Prepare and update metadata.
		$tag_updater = new \WP_Tag_Order\Tag_Updater();
		$result      = $tag_updater->update_tag_order( $post_id, $taxonomy, $tags );

		// Update post terms.
		$term_taxonomy_ids = wp_set_object_terms( $post_id, $tags, $taxonomy );

		// Handle metadata update failure.
		if ( false === $result ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'code'    => 'meta_update_failed',
					'message' => __( 'Failed to update tag order metadata.', 'wp-tag-order' ),
					'data'    => array(
						'status'   => 500,
						'post_id'  => $post_id,
						'taxonomy' => $taxonomy,
					),
				)
			);
		}

		// Handle term update failure.
		if ( is_wp_error( $term_taxonomy_ids ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'code'    => 'term_update_failed',
					'message' => __( 'Failed to update post terms.', 'wp-tag-order' ),
					'data'    => array(
						'status'        => 500,
						'post_id'       => $post_id,
						'taxonomy'      => $taxonomy,
						'error_message' => $term_taxonomy_ids->get_error_message(),
						'error_code'    => $term_taxonomy_ids->get_error_code(),
					),
				)
			);
		}

		// Successful update response.
		return rest_ensure_response(
			array(
				'success' => true,
				'code'    => 'tags_order_updated',
				'message' => __( 'Tag order updated successfully.', 'wp-tag-order' ),
				'data'    => array(
					'status'   => 200,
					'post_id'  => $post_id,
					'taxonomy' => $taxonomy,
					'tags'     => $tags,
				),
			)
		);

	} catch ( InvalidArgumentException $e ) {
		// Handle input parameter casting failures.
		return rest_ensure_response(
			array(
				'success' => false,
				'code'    => 'invalid_input',
				'message' => __( 'Invalid input parameters.', 'wp-tag-order' ),
				'data'    => array(
					'status'        => 400,
					'error_message' => $e->getMessage(),
				),
			)
		);
	} catch ( \Exception $e ) {
		// Handle unexpected errors.
		return rest_ensure_response(
			array(
				'success' => false,
				'code'    => 'unexpected_error',
				'message' => __( 'An unexpected error occurred.', 'wp-tag-order' ),
				'data'    => array(
					'status'        => 500,
					'error_message' => $e->getMessage(),
				),
			)
		);
	}
}

/**
 * Permission check for taxonomies REST API endpoints.
 *
 * @param \WP_REST_Request $request REST request object.
 * @return true
 *
 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
 */
function wp_tag_order_rest_taxonomies_permission_check( \WP_REST_Request $request ): bool {
	// Allow read access to enabled taxonomies for all users.
	// This is safe as it only exposes which taxonomies have tag ordering enabled,
	// which is not sensitive information.
	return true;
}

/**
 * Get enabled taxonomies endpoint.
 *
 * Retrieves the list of taxonomies that have tag ordering enabled.
 * Returns both enabled taxonomies and additional metadata.
 *
 * @param \WP_REST_Request $request REST request object.
 * @return \WP_REST_Response
 *
 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
 */
function wp_tag_order_get_enabled_taxonomies_endpoint( \WP_REST_Request $request ): \WP_REST_Response {
	try {
		// Get enabled taxonomies.
		$enabled_taxonomies = apply_filters( 'wp_tag_order_enabled_taxonomies', wp_tag_order_get_enabled_taxonomies() );

		// Get all non-hierarchical taxonomies for reference.
		$available_taxonomies     = apply_filters( 'wp_tag_order_non_hierarchical_taxonomies', wp_tag_order_get_non_hierarchical_taxonomies() );
		$available_taxonomy_names = array_values(
			array_filter(
				array_map(
					function ( $taxonomy ) {
						// Validate that $taxonomy is an object with a name property.
						if ( ! is_object( $taxonomy ) || ! isset( $taxonomy->name ) ) {
							return null;
						}
						return $taxonomy->name;
					},
					$available_taxonomies
				)
			)
		);

		// Build response.
		$response_data = array(
			'enabled_taxonomies'   => $enabled_taxonomies,
			'available_taxonomies' => $available_taxonomy_names,
			'meta'                 => array(
				'enabled_count'   => count( $enabled_taxonomies ),
				'available_count' => count( $available_taxonomy_names ),
				'timestamp'       => gmdate( 'c' ),
			),
		);

		return rest_ensure_response( $response_data );

	} catch ( \Exception $e ) {
		// Handle unexpected errors.
		return rest_ensure_response(
			array(
				'success' => false,
				'code'    => 'unexpected_error',
				'message' => __( 'Failed to retrieve enabled taxonomies.', 'wp-tag-order' ),
				'data'    => array(
					'status'        => 500,
					'error_message' => $e->getMessage(),
				),
			)
		);
	}
}
