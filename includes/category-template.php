<?php
/**
 * Template for catogory.
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
 * Get the ordered terms.
 *
 * @param string $post_id "description".
 * @param string $taxonomy "description".
 *
 * @return statement "description".
 */
function get_the_terms_ordered( $post_id, $taxonomy ) {
	global $post;

	if ( ! $post_id ) {
		$post_id = $post->ID;
	}
	$ids = get_post_meta( $post_id, 'wp-tag-order-' . $taxonomy, true );
	if ( $ids ) {
		$return = array();
		$ids    = unserialize( $ids );
		foreach ( $ids as $tagid ) {
			$tag      = get_term_by( 'id', $tagid, $taxonomy );
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
				'term_order'       => $tag->term_order,
			);
		}
	} else {
		$return = false;
	}

	return apply_filters( 'get_the_tags', $return );
}

/**
 * Get the ordered tags.
 *
 * @param string $post_id "description".
 *
 * @return array "description".
 */
function get_the_tags_ordered( $post_id = '' ) {
	return get_the_terms_ordered( $post_id, 'post_tag' );
}

/**
 * Retrieve the tags for a post formatted as a string.
 *
 * @param string $before Optional. Before tags.
 * @param string $sep Optional. Between tags.
 * @param string $after Optional. After tags.
 * @param int    $id Optional. Post ID. Defaults to the current post.
 * @return string|false|WP_Error A list of tags on success, false if there are no terms, WP_Error on failure.
 */
function get_the_tag_list_ordered( $before = '', $sep = '', $after = '', $id = 0 ) {

	/**
	 * Filters the tags list for a given post.
	 *
	 * @since 2.3.0
	 *
	 * @param string $tag_list List of tags.
	 * @param string $before   String to use before tags.
	 * @param string $sep      String to use between the tags.
	 * @param string $after    String to use after tags.
	 * @param int    $id       Post ID.
	 */
	return apply_filters( 'the_tags', get_the_term_list_ordered( $id, 'post_tag', $before, $sep, $after ), $before, $sep, $after, $id );
}

/**
 * Retrieve the tags for a post.
 *
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 */
function the_tags_ordered( $before = null, $sep = ', ', $after = '' ) {
	if ( null === $before ) {
		$before = __( 'Tags: ' );
	}
	echo get_the_tag_list_ordered( $before, $sep, $after );
}

/**
 * Retrieve a post's terms as a list with specified format.
 *
 * @param int    $id Post ID.
 * @param string $taxonomy Taxonomy name.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 * @return string|false|WP_Error A list of terms on success, false if there are no terms, WP_Error on failure.
 */
function get_the_term_list_ordered( $id, $taxonomy, $before = '', $sep = '', $after = '' ) {
	$terms = get_the_terms_ordered( $id, $taxonomy );

	if ( is_wp_error( $terms ) ) {
		return $terms;
	}

	if ( empty( $terms ) ) {
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
 * Display the terms in a list.
 *
 * @param int    $id Post ID.
 * @param string $taxonomy Taxonomy name.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 * @return false|void False on WordPress error.
 */
function the_terms_ordered( $id, $taxonomy, $before = '', $sep = ', ', $after = '' ) {
	$term_list = get_the_term_list_ordered( $id, $taxonomy, $before, $sep, $after );

	if ( is_wp_error( $term_list ) ) {
		return false;
	}

	/**
	 * Filters the list of terms to display.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $term_list List of terms to display.
	 * @param string $taxonomy  The taxonomy name.
	 * @param string $before    String to use before the terms.
	 * @param string $sep       String to use between the terms.
	 * @param string $after     String to use after the terms.
	 */
	echo apply_filters( 'the_terms', $term_list, $taxonomy, $before, $sep, $after );
}
