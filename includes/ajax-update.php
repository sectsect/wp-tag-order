<?php
/**
 * Update post_meta via Ajax.
 *
 * @link       https://www.ilovesect.com/
 * @since      1.0.0
 *
 * @package    WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 */

/**
 * Update post_meta via Ajax.
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

if ( $id ) {
	$newordertags = explode( ',', sanitize_text_field( wp_unslash( $tags ) ) );
	if ( isset( $newordertags ) ) {
		$meta_box_tags_value = serialize( $newordertags );
	}
	$return = update_post_meta( sanitize_text_field( wp_unslash( $id ) ), 'wp-tag-order-' . sanitize_text_field( wp_unslash( $taxonomy ) ), $meta_box_tags_value );
} else {
	$return = false;
}

echo json_encode( $return );
exit;
