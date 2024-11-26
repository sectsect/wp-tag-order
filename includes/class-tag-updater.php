<?php
/**
 * WP Tag Order Tag Updater Class
 *
 * Provides a method to update tag order for a specific post and taxonomy.
 *
 * @package WP_Tag_Order
 * @since 3.11.0
 */

declare(strict_types=1);

namespace WP_Tag_Order;

/**
 * Class responsible for updating tag order for a specific post.
 */
class Tag_Updater {
	/**
	 * Update tag order for a specific post and taxonomy.
	 *
	 * @param int               $post_id  The post ID to update tags for.
	 * @param string            $taxonomy The taxonomy name.
	 * @param array<int>|string $tag_ids  Array of tag IDs or comma-separated tag IDs.
	 *
	 * @return int|bool True if meta update was successful, false otherwise.
	 * @throws \InvalidArgumentException If input validation fails.
	 */
	public function update_tag_order( int $post_id, string $taxonomy, array|string $tag_ids ): int|bool {
		// Validate inputs.
		if ( $post_id <= 0 ) {
			throw new \InvalidArgumentException( 'Invalid post ID' );
		}

		if ( empty( $taxonomy ) ) {
			throw new \InvalidArgumentException( 'Taxonomy cannot be empty' );
		}

		if ( ! taxonomy_exists( $taxonomy ) ) {
			throw new \InvalidArgumentException( sprintf( 'Invalid taxonomy: %s', esc_html( $taxonomy ) ) );
		}

		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			throw new \InvalidArgumentException( sprintf( 'Taxonomy %s is hierarchical and not supported', esc_html( $taxonomy ) ) );
		}

		// Convert string to array if needed.
		if ( is_string( $tag_ids ) ) {
			$tag_ids = explode( ',', wp_unslash( $tag_ids ) );
		}

		if ( empty( $tag_ids ) ) {
			throw new \InvalidArgumentException( 'Tag IDs cannot be empty' );
		}

		if ( ! wto_is_enabled_taxonomy( $taxonomy ) ) {
			throw new \InvalidArgumentException( sprintf( 'Taxonomy %s is not enabled for tag ordering', esc_html( $taxonomy ) ) );
		}

		// Sanitize tag IDs.
		$sanitized_tag_ids = array_map(
			function ( $tag_id ): int {
				// Ensure each tag ID is a positive integer.
				$sanitized_id = filter_var(
					$tag_id,
					FILTER_VALIDATE_INT,
					array(
						'options' => array(
							'min_range' => 1,
						),
					)
				);

				if ( false === $sanitized_id ) {
					throw new \InvalidArgumentException( 'Invalid tag ID: ' . esc_html( is_string( $tag_id ) ? $tag_id : (string) $tag_id ) );
				}

				return $sanitized_id;
			},
			$tag_ids
		);

		// Serialize tags for storage.
		$meta_box_tags_value = serialize( $sanitized_tag_ids );

		// Update post meta.
		return update_post_meta( $post_id, 'wp-tag-order-' . $taxonomy, $meta_box_tags_value );
	}
}
