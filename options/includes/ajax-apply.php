<?php
/**
 * For Options Page.
 *
 * @link       https://www.ilovesect.com/
 * @since      1.0.0
 *
 * @package    WP_Tag_Order
 * @subpackage WP_Tag_Order/options/includes
 */

/**
 * Template for catogory.
 *
 * @package    WP_Tag_Order
 * @subpackage WP_Tag_Order/options/includes
 */

	require('../../../../../wp-load.php');
	require_once plugin_dir_path(dirname(dirname(__FILE__))) . 'includes/functions.php';

	extract($_POST, EXTR_SKIP);
	if(!isset($nonce) || empty($nonce) || !wp_verify_nonce($nonce, 'wpto-options') || $_SERVER["REQUEST_METHOD"] != "POST"){
		wp_safe_redirect(home_url('/'), 301);
		exit;
	}

	$count = 0;
	$pts = wto_has_tag_posttype();
	foreach ( $pts as $pt ) {
		$ids = array();
		$myQuery = new WP_Query();
		$param    = array(
			'post_type'      => $pt,
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => array( 'any', 'trash', 'auto-draft' ),
		);
		$myQuery->query( $param );
		if( $myQuery->have_posts() ): while( $myQuery->have_posts() ) : $myQuery->the_post();
			array_push( $ids, $post->ID );
		endwhile; endif; wp_reset_postdata();

		foreach ( $ids as $postid ) {
			$taxonomies = get_object_taxonomies( $pt );
			if( ! empty( $taxonomies ) ) {
				foreach( $taxonomies as $taxonomy ) {
					if ( ! is_taxonomy_hierarchical( $taxonomy ) && $taxonomy !== 'post_format' ) {
						$terms = get_the_terms( $postid, $taxonomy );
						$meta = get_post_meta( $postid, 'wp-tag-order-' . $taxonomy, true );
						if ( ! empty( $terms ) && ! $meta ) {
							$term_ids = array();
							foreach ( $terms as $term ) {
								array_push( $term_ids, $term->term_id );
							}
							$meta_box_tags_value = serialize( $term_ids );
							$return = update_post_meta( $postid, 'wp-tag-order-' . $taxonomy, $meta_box_tags_value );
							if ( $return ) {
								$count++;
							}
						}
				    }
				}
			}
		}
	}

	$return = $count;

	echo json_encode( $return );
	exit;
