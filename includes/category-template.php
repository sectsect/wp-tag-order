<?php
/**
 * Template for category.
 *
 * @since 1.0.0
 *
 * @package WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 */

declare(strict_types=1);

/**
 * Retrieves the ordered terms for a given post and specified taxonomy.
 * This function fetches the post object, retrieves the stored term IDs from post meta,
 * and constructs an array of term objects in the order specified in the post meta.
 *
 * @param int|WP_Post $post  The ID of the post or the WP_Post object itself.
 * @param string      $taxonomy The taxonomy name for which terms are to be retrieved.
 *
 * @return WP_Term[]|false An array of term objects on success, or false if no terms are found or the post does not exist.
 */
function get_the_terms_ordered( int|WP_Post $post, string $taxonomy ): array|false {
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}

	$ids = get_post_meta( intval( $post->ID ), 'wp-tag-order-' . $taxonomy, true );
	if ( ! is_string( $ids ) ) {
		return false; // Ensure that $ids is a string before unserializing.
	}

	$ids = unserialize( $ids );
	if ( ! is_array( $ids ) ) {
		return false; // Ensure that $ids is an array before iterating.
	}

	$return = array();
	foreach ( $ids as $tagid ) {
		if ( ! is_numeric( $tagid ) ) {
			continue; // Ensure that $tagid is an int or string numeric before passing to get_term_by.
		}
		$tag = get_term_by( 'id', (int) $tagid, $taxonomy );
		if ( ! $tag || is_wp_error( $tag ) ) {
			continue; // Skip if the term is not found or an error occurred.
		}
		$return[] = (object) array(
			'term_id'          => $tag->term_id,
			'name'             => $tag->name,
			'slug'             => $tag->slug,
			'term_group'       => $tag->term_group,
			'term_taxonomy_id' => $tag->term_taxonomy_id,
			'taxonomy'         => $tag->taxonomy,
			'description'      => $tag->description,
			'parent'           => $tag->parent,
			'count'            => $tag->count,
			'filter'           => $tag->filter,
		);
	}

	return apply_filters( 'get_the_tags', $return );
}

/**
 * Retrieves the ordered tags for a given post.
 * This function is a wrapper for get_the_terms_ordered, specifically for the 'post_tag' taxonomy.
 *
 * @param int|WP_Post $post The ID of the post or the WP_Post object itself. Defaults to the current post if not specified.
 *
 * @return WP_Term[]|false An array of tag objects on success, or false if no tags are found.
 */
function get_the_tags_ordered( int|WP_Post $post = 0 ): array|false {
	return get_the_terms_ordered( $post, 'post_tag' );
}

/**
 * Retrieves a formatted string of ordered tags for a specified post.
 *
 * This function fetches the tags associated with a given post in the order they are stored and formats them as a string.
 * The tags are retrieved using the `get_the_term_list_ordered` function specifically for the 'post_tag' taxonomy.
 *
 * @param string $before Optional. The string to prepend before the tag list. Defaults to an empty string.
 * @param string $sep    Optional. The separator string between individual tags. Defaults to an empty string.
 * @param string $after  Optional. The string to append after the tag list. Defaults to an empty string.
 * @param int    $id     Optional. The ID of the post for which to retrieve tags. Defaults to the current post if not specified.
 *
 * @return string|false|WP_Error The formatted tag list as a string on success, false if no tags are found, or WP_Error on failure.
 */
function get_the_tag_list_ordered( string $before = '', string $sep = '', string $after = '', int $id = 0 ): string|false|WP_Error {
	$tag_list = get_the_term_list_ordered( $id, 'post_tag', $before, $sep, $after );

	/**
	 * Filters the formatted tag list for a post.
	 *
	 * Allows modification of the final output of tag list string before it is returned.
	 *
	 * @since 2.3.0
	 *
	 * @param string|false|WP_Error $tag_list The formatted list of tags.
	 * @param string $before   The string used before the tag list.
	 * @param string $sep      The separator used between tags.
	 * @param string $after    The string used after the tag list.
	 * @param int    $id       The ID of the post.
	 */
	return apply_filters( 'the_tags', $tag_list, $before, $sep, $after, $id );
}

/**
 * Displays the tags for a post.
 *
 * @param string $before Optional. String to use before the tags.
 * @param string $sep    Optional. String to use between the tags.
 * @param string $after  Optional. String to use after the tags.
 *
 * @return void
 */
function the_tags_ordered( ?string $before = null, string $sep = ', ', string $after = '' ): void {
	if ( null === $before ) {
		$before = __( 'Tags: ' );
	}
	$tag_list = get_the_tag_list_ordered( $before, $sep, $after );

	if ( is_string( $tag_list ) ) {
		echo $tag_list;
	} elseif ( is_wp_error( $tag_list ) ) {
		// Log the error or display an error message.
		error_log( 'Error retrieving tags: ' . $tag_list->get_error_message() );
	} else {
		// Handle the case where no tags are found.
		echo 'No tags found.';
	}
}

/**
 * Retrieves a post's terms as a list with specified format.
 *
 * @param int    $id       The ID of the post.
 * @param string $taxonomy The taxonomy name.
 * @param string $before   Optional. String to use before the term list.
 * @param string $sep      Optional. String to use between the terms.
 * @param string $after    Optional. String to use after the term list.
 *
 * @return string|false|WP_Error A list of terms on success, false if there are no terms, WP_Error on failure.
 */
function get_the_term_list_ordered( int $id, string $taxonomy, string $before = '', string $sep = '', string $after = '' ): string|false|WP_Error {
	$terms = get_the_terms_ordered( $id, $taxonomy );

	if ( false === $terms ) {
		return false;
	}

	$links = array();

	foreach ( $terms as $term ) {
		$link = get_term_link( $term, $taxonomy );
		if ( is_wp_error( $link ) ) {
			return $link;
		}
		$links[] = '<a href="' . esc_url( $link ) . '" rel="tag">' . $term->name . '</a>';
	}

	/**
	 * Filters the term links for a given taxonomy.
	 *
	 * The dynamic portion of the filter name, `$taxonomy`, refers
	 * to the taxonomy slug.
	 *
	 * @since 2.5.0
	 *
	 * @param array $links An array of term links.
	 */
	$term_links = apply_filters( "term_links-$taxonomy", $links );

	return $before . join( $sep, $term_links ) . $after;
}

/**
 * Displays the terms for a given post and taxonomy.
 *
 * @param int    $id       The ID of the post.
 * @param string $taxonomy The taxonomy name.
 * @param string $before   Optional. String to use before the term list.
 * @param string $sep      Optional. String to use between the terms.
 * @param string $after    Optional. String to use after the term list.
 *
 * @return string|WP_Error|false
 */
function the_terms_ordered( int $id, string $taxonomy, string $before = '', string $sep = ', ', string $after = '' ) {
	$term_list = get_the_term_list_ordered( $id, $taxonomy, $before, $sep, $after );

	if ( false === $term_list ) {
		return false;
	}

	/**
	 * Filters the list of terms to display.
	 *
	 * @since 2.9.0
	 *
	 * @param string|false|WP_Error $term_list List of terms to display.
	 * @param string                $taxonomy  The taxonomy name.
	 * @param string                $before    String to use before the terms.
	 * @param string                $sep       String to use between the terms.
	 * @param string                $after     String to use after the terms.
	 */
	return apply_filters( 'the_terms', $term_list, $taxonomy, $before, $sep, $after );
}
