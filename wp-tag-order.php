<?php
/**
 * Plugin Name:     WP Tag Order
 * Plugin URI:      https://github.com/sectsect/wp-tag-order
 * Description:     WP Tag Order plugin will order tags, non-hierarchical custom-taxonomy terms in individual posts with simple Drag and Drop Sortable capability. And supplies some functions to output it.
 * Author:          SECT INTERACTIVE AGENCY
 * Author URI:      https://www.ilovesect.com/
 * Text Domain:     wp-tag-order
 * Domain Path:     /languages
 * Version:         1.2.3
 *
 * @package         WP_Tag_Order
 */

$wptagorder_minimalrequiredphpversion = '5.3';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version.
 *
 * @return void "description".
 */
function wptagorder_noticephpversionwrong() {
	global $wptagorder_minimalrequiredphpversion;
	echo '<div class="updated fade">' .
	__( 'Error: plugin "WP Tag Order" requires a newer version of PHP to be running.', 'wp_instagram_json' ) .
			'<br/>' . __( 'Minimal version of PHP required: ', 'wp-tag-order' ) . '<strong>' . $wptagorder_minimalrequiredphpversion . '</strong>' .
			'<br/>' . __( 'Your server\'s PHP version: ', 'wp-tag-order' ) . '<strong>' . phpversion() . '</strong>' .
		'</div>';
}

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version.
 *
 * @return boolean "description".
 */
function wptagorder_phpversioncheck() {
	global $wptagorder_minimalrequiredphpversion;
	if ( version_compare( phpversion(), $wptagorder_minimalrequiredphpversion ) < 0 ) {
		add_action( 'admin_notices', 'wptagorder_noticephpversionwrong' );
		return false;
	}
	return true;
}

/**
 * Load the textdomain.
 *
 * @return void "description".
 */
function wptagorder_load_textdomain() {
	load_plugin_textdomain( 'wp-tag-order', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wptagorder_load_textdomain' );

/**
 * Add my meta data to row.
 *
 * @param  array  $plugin_meta "description".
 * @param  string $plugin_file "description".
 * @param  string $plugin_data "description".
 * @param  string $status      "description".
 * @return statement           "description".
 */
function my_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
	if ( plugin_basename( __FILE__ ) === $plugin_file ) {
		$plugin_meta[] = '<a href="https://github.com/sectsect/wp-tag-order" target="_blank"><span class="dashicons dashicons-randomize"></span> GitHub</a>';
	}
	return $plugin_meta;
}
add_filter( 'plugin_row_meta', 'my_plugin_row_meta', 10, 4 );

if ( wptagorder_phpversioncheck() ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/category-template.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/index.php';
}
