<?php
/**
 * For Options Page.
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

require '../../../../wp-load.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';

$id       = $_POST['id'];
$nonce    = $_POST['nonce'];
$taxonomy = $_POST['taxonomy'];
$tags     = $_POST['tags'];

if ( ! isset( $tags ) || ! isset( $nonce ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wpto' ) || 'POST' != $_SERVER['REQUEST_METHOD'] ) {
	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}

$newtags    = explode( ',', esc_attr( wp_unslash( $tags ) ) );
$newtagsids = array();
foreach ( $newtags as $newtag ) {
	$term = term_exists( $newtag, sanitize_text_field( wp_unslash( $taxonomy ) ) );
	if ( 0 == $term && null == $term ) {
		$term_taxonomy_ids = wp_set_object_terms( sanitize_text_field( wp_unslash( $id ) ), $newtag, sanitize_text_field( wp_unslash( $taxonomy ) ), true );
		if ( is_wp_error( $term_taxonomy_ids ) ) {
			exit;
		}
	}
	$tag = get_term_by( 'name', $newtag, sanitize_text_field( wp_unslash( $taxonomy ) ) );
	array_push( $newtagsids, (string) $tag->term_id );
}

if ( $id ) {
	$savedata = array();
	$tags_val = get_post_meta( sanitize_text_field( wp_unslash( $id ) ), 'wp-tag-order-' . sanitize_text_field( wp_unslash( $taxonomy ) ), true );
	if ( ! wto_is_array_empty( $tags_val ) ) {
		$basetagsids = unserialize( $tags_val );
		$added       = array_diff_interactive( $newtagsids, $basetagsids );
		foreach ( $added as $val ) {
			if ( ! in_array( $val, $basetagsids ) ) {
				array_push( $basetagsids, $val );
			} else {
				$key = array_search( $val, $basetagsids );
				if ( false !== $key ) {
					unset( $basetagsids[ $key ] );
				}
			}
		}
		$savedata = $basetagsids;
	} else {
		$savedata = $newtagsids;
	}
	// Update the DB in real time (wp_postmeta) !
	if ( isset( $savedata ) ) {
		$meta_box_tags_value = serialize( $savedata );
	}
	$return = update_post_meta( sanitize_text_field( wp_unslash( $id ) ), 'wp-tag-order-' . sanitize_text_field( wp_unslash( $taxonomy ) ), $meta_box_tags_value );

	// Update the DB in real time (wp_term_relationships) !
	$newtagsids_int    = array_map( 'intval', $newtagsids ); // Cast string to integer	@ Line: 23 !
	$term_taxonomy_ids = wp_set_object_terms( sanitize_text_field( wp_unslash( $id ) ), $newtagsids_int, sanitize_text_field( wp_unslash( $taxonomy ) ) );
	if ( is_wp_error( $term_taxonomy_ids ) ) {
		exit;
	}
} else {
	$savedata = $newtagsids;
}

$return = '';
if ( ! wto_is_array_empty( $savedata ) ) {
	foreach ( $savedata as $newtag ) {
		$tag     = get_term_by( 'id', esc_attr( $newtag ), sanitize_text_field( wp_unslash( $taxonomy ) ) );
		$return .= '<li><input type="text" readonly="readonly" value="' . esc_attr( $tag->name ) . '"><input type="hidden" name="wp-tag-order-' . esc_attr( wp_unslash( $taxonomy ) ) . '[]" value="' . esc_attr( $tag->term_id ) . '"></li>';
	}
}

echo json_encode( $return );
exit;
