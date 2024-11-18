<?php
/**
 * REST API Endpoints for WP Tag Order plugin.
 *
 * @package WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 * @since 3.9.0
 */

declare(strict_types=1);

/**
 * Register REST API endpoints for tag ordering.
 *
 * @return void
 */
function wpto_register_rest_endpoints(): void {
	register_rest_route(
		'wp-tag-order/v1',
		'/tags/order/(?P<post_id>\d+)',
		array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => 'wpto_get_post_tag_order',
			'permission_callback' => 'wpto_rest_permission_check',
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
		'wp-tag-order/v1',
		'/tags/order/(?P<post_id>\d+)',
		array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => 'wpto_update_post_tag_order',
			'permission_callback' => 'wpto_rest_permission_check',
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
						return wpto_validate_taxonomy( $value );
					},
				),
				'tags'     => array(
					'required'          => true,
					'type'              => 'string',
					'validate_callback' => function ( $value, $request, $key ) {
						$taxonomy = $request->get_param( 'taxonomy' );
						return wpto_validate_tag_ids( $value, $taxonomy );
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'wpto_register_rest_endpoints' );

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
function wpto_validate_taxonomy( string $taxonomy ): bool {
	// Check if taxonomy exists in WordPress.
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return false;
	}

	// Check if taxonomy is enabled for the plugin.
	if ( ! wto_is_enabled_taxonomy( $taxonomy ) ) {
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
function wpto_validate_tag_ids( string $tags, string $taxonomy ): bool {
	// Split tags and convert to integers.
	$tag_array = array_map( 'intval', explode( ',', $tags ) );

	// Check for non-numeric values.
	$non_numeric_tags = array_filter(
		$tag_array,
		function ( $tag ) {
			return ! is_numeric( $tag ) || $tag <= 0;
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
function wpto_rest_permission_check( \WP_REST_Request $request ): bool {
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
		$post_id = wpto_cast_mixed_to_int( $request->get_param( 'post_id' ) );
	} catch ( InvalidArgumentException $e ) {
		return false;
	}

	// Check if post exists.
	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}

	// Restrict to allowed post types.
	$allowed_post_types = apply_filters( 'wpto_allowed_post_types', array( 'post', 'page' ) );
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
 * @param \WP_REST_Request $request REST request object.
 * @return \WP_REST_Response|\WP_Error
 *
 * @phpstan-param WP_REST_Request<array{post_id?: int, taxonomy?: string}> $request
 */
function wpto_get_post_tag_order( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
	$post_id  = wpto_cast_mixed_to_int( $request->get_param( 'post_id' ) );
	$taxonomy = $request->get_param( 'taxonomy' ) ?? 'post_tag';

	$tags_value   = get_post_meta( $post_id, 'wp-tag-order-' . $taxonomy, true );
	$tags         = is_string( $tags_value ) ? unserialize( $tags_value ) : array();
	$ordered_tags = array_map(
		function ( int $tag_id ) use ( $taxonomy ): ?array {
			if ( ! is_string( $taxonomy ) ) {
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
 * @return \WP_REST_Response|\WP_Error
 *
 * @phpstan-param WP_REST_Request<array{post_id?: int, taxonomy?: string, tags?: string}> $request
 */
function wpto_update_post_tag_order( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
	try {
		// Cast and validate input parameters.
		$post_id  = wpto_cast_mixed_to_int( $request->get_param( 'post_id' ) );
		$taxonomy = wpto_cast_mixed_to_string( $request->get_param( 'taxonomy' ) );
		$tags     = explode( ',', wpto_cast_mixed_to_string( $request->get_param( 'tags' ) ) );
		$tags     = wpto_cast_mixed_to_int_array( $tags );

		// Validate taxonomy.
		if ( ! wpto_validate_taxonomy( $taxonomy ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'code'    => 'invalid_taxonomy',
					'message' => __( 'Invalid or unsupported taxonomy.', 'wpto' ),
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
					'message' => __( 'One or more tag IDs are invalid.', 'wpto' ),
					'data'    => array(
						'status'       => 400,
						'invalid_tags' => $invalid_tags,
						'taxonomy'     => $taxonomy,
					),
				)
			);
		}

		// Retrieve current tag order.
		$current_tags_value = get_post_meta( $post_id, 'wp-tag-order-' . $taxonomy, true );
		$current_tags       = wpto_cast_mixed_to_array(
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
					'message' => __( 'No changes in tag order detected.', 'wpto' ),
				)
			);
		}

		// Prepare and update metadata.
		$meta_box_tags_value = serialize( $tags );
		$meta_result         = update_post_meta( $post_id, 'wp-tag-order-' . $taxonomy, $meta_box_tags_value );

		// Update post terms.
		$term_taxonomy_ids = wp_set_object_terms( $post_id, $tags, $taxonomy );

		// Handle metadata update failure.
		if ( false === $meta_result ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'code'    => 'meta_update_failed',
					'message' => __( 'Failed to update tag order metadata.', 'wpto' ),
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
					'message' => __( 'Failed to update post terms.', 'wpto' ),
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
				'message' => __( 'Tag order updated successfully.', 'wpto' ),
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
				'message' => __( 'Invalid input parameters.', 'wpto' ),
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
				'message' => __( 'An unexpected error occurred.', 'wpto' ),
				'data'    => array(
					'status'        => 500,
					'error_message' => $e->getMessage(),
				),
			)
		);
	}
}
