<?php
/**
 * Plugin Name:     WP Tag Order
 * Plugin URI:      https://github.com/sectsect/wp-tag-order
 * Description:     📦 ↕︎ Order tags (Non-hierarchical custom taxonomies) within individual posts with simple Drag-and-Drop sortable feature.
 * Author:          sect
 * Author URI:      https://github.com/sectsect
 * Text Domain:     wp-tag-order
 * Domain Path:     /languages
 * Version:         3.11.2
 * License:         GPL-3.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package         WP_Tag_Order
 */

declare(strict_types=1);

$wptagorder_minimalrequiredphpversion = '8.0';

/**
 * Displays an admin notice if the PHP version is less than the required version.
 *
 * @return void
 */
function wptagorder_noticephpversionwrong(): void {
	global $wptagorder_minimalrequiredphpversion;
	// Ensure $wptagorder_minimalrequiredphpversion is not null and is a string.
	$required_version = is_null( $wptagorder_minimalrequiredphpversion ) ? 'unknown' : $wptagorder_minimalrequiredphpversion;
	echo '<div class="updated fade">' .
	esc_html__( 'Error: plugin "WP Tag Order" requires a newer version of PHP to be running.', 'wp-tag-order' ) .
		'<br/>' . esc_html__( 'Minimal version of PHP required: ', 'wp-tag-order' ) . '<strong>' . esc_html( $required_version ) . '</strong>' .
		'<br/>' . esc_html__( 'Your server\'s PHP version: ', 'wp-tag-order' ) . '<strong>' . esc_html( phpversion() ) . '</strong>' .
	'</div>';
}

/**
 * Checks if the PHP version meets the minimum required version.
 *
 * @return bool True if the PHP version is sufficient, false otherwise.
 */
function wptagorder_phpversioncheck(): bool {
	global $wptagorder_minimalrequiredphpversion;
	// Ensure $wptagorder_minimalrequiredphpversion is not null before comparison.
	if ( null === $wptagorder_minimalrequiredphpversion || version_compare( phpversion(), $wptagorder_minimalrequiredphpversion ) < 0 ) {
		add_action( 'admin_notices', 'wptagorder_noticephpversionwrong' );
		return false;
	}
	return true;
}

/**
 * Loads the plugin's text domain for localization.
 *
 * @return void
 */
function wptagorder_load_textdomain(): void {
	load_plugin_textdomain( 'wp-tag-order', false, plugin_basename( __DIR__ ) . '/languages' );
}
add_action( 'plugins_loaded', 'wptagorder_load_textdomain' );

/**
 * Adds custom meta data to the plugin's row in the plugins list table.
 *
 * @param string[]             $plugin_meta An array of the plugin's metadata.
 * @param string               $plugin_file Path to the plugin file relative to the plugins directory.
 * @param array<string, mixed> $plugin_data An array of plugin data.
 * @param string               $status Status of the plugin.
 *
 * @return string[] The modified plugin metadata array.
 */
function my_plugin_row_meta( array $plugin_meta, string $plugin_file, array $plugin_data, string $status ): array {
	if ( plugin_basename( __FILE__ ) === $plugin_file ) {
		$plugin_meta[] = '<a href="https://github.com/sectsect/wp-tag-order" target="_blank"><span class="dashicons dashicons-randomize"></span> GitHub</a>';
	}
	return $plugin_meta;
}
add_filter( 'plugin_row_meta', 'my_plugin_row_meta', 10, 4 );

if ( wptagorder_phpversioncheck() ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tag-updater.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/category-template.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/index.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/rest-api.php';
}
