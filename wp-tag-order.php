<?php
/**
 * Plugin Name:     WP Tag Order
 * Plugin URI:      https://github.com/sectsect/wp-tag-order
 * Description:     📦 ↕︎ Order tags (Non-hierarchical custom taxonomies) within individual posts with simple Drag-and-Drop sortable feature.
 * Author:          sect
 * Author URI:      https://github.com/sectsect
 * Text Domain:     wp-tag-order
 * Domain Path:     /languages
 * Version:         3.13.0
 * License:         GPL-3.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package         WP_Tag_Order
 */

declare(strict_types=1);

const WP_TAG_ORDER_MINIMAL_REQUIRED_PHP_VERSION = '8.0';

/**
 * Displays an admin notice if the PHP version is less than the required version.
 *
 * @return void
 */
function wp_tag_order_notice_php_version_wrong(): void {
	// Get the required version, defaulting to 'unknown' if not defined.
	$required_version = defined( 'WP_TAG_ORDER_MINIMAL_REQUIRED_PHP_VERSION' )
		? WP_TAG_ORDER_MINIMAL_REQUIRED_PHP_VERSION
		: 'unknown';

	$current_version = phpversion();

	// Build the notice message with proper escaping.
	$message = sprintf(
		'<div class="notice notice-error"><p>%s<br/>%s<strong>%s</strong><br/>%s<strong>%s</strong></p></div>',
		esc_html__( 'Error: plugin "WP Tag Order" requires a newer version of PHP to be running.', 'wp-tag-order' ),
		esc_html__( 'Minimal version of PHP required: ', 'wp-tag-order' ),
		esc_html( $required_version ),
		esc_html__( 'Your server\'s PHP version: ', 'wp-tag-order' ),
		esc_html( $current_version )
	);

	echo wp_kses_post( $message );
}

/**
 * Checks if the PHP version meets the minimum required version.
 * This is a pure function that only performs version comparison.
 *
 * @return bool True if the PHP version is sufficient, false otherwise.
 */
function wp_tag_order_check_php_version(): bool {
	// Ensure the required version constant is defined.
	if ( ! defined( 'WP_TAG_ORDER_MINIMAL_REQUIRED_PHP_VERSION' ) ) {
		return false;
	}

	$current_version  = phpversion();
	$required_version = WP_TAG_ORDER_MINIMAL_REQUIRED_PHP_VERSION;

	return version_compare( $current_version, $required_version, '>=' );
}

/**
 * Handles PHP version error by adding admin notice.
 * This function is responsible for the side effect of displaying the error.
 *
 * @return void
 */
function wp_tag_order_handle_php_version_error(): void {
	if ( ! wp_tag_order_check_php_version() ) {
		add_action( 'admin_notices', 'wp_tag_order_notice_php_version_wrong' );
	}
}

/**
 * Checks if the PHP version meets the minimum required version.
 * This function maintains backward compatibility while using the new implementation.
 *
 * @return bool True if the PHP version is sufficient, false otherwise.
 */
function wp_tag_order_php_version_check(): bool {
	$is_version_sufficient = wp_tag_order_check_php_version();

	if ( ! $is_version_sufficient ) {
		wp_tag_order_handle_php_version_error();
	}

	return $is_version_sufficient;
}

/**
 * Adds custom GitHub link to the WP Tag Order plugin's row in the plugins list table.
 *
 * @param array<string>        $plugin_meta An array of the plugin's metadata.
 * @param string               $plugin_file Path to the plugin file relative to the plugins directory.
 * @param array<string, mixed> $plugin_data An array of plugin data.
 * @param string               $status Status of the plugin.
 *
 * @return array<string> The modified plugin metadata array.
 */
function wp_tag_order_add_github_link( array $plugin_meta, string $plugin_file, array $plugin_data, string $status ): array {
	if ( plugin_basename( __FILE__ ) === $plugin_file ) {
		$plugin_meta[] = '<a href="https://github.com/sectsect/wp-tag-order" target="_blank"><span class="dashicons dashicons-randomize"></span> GitHub</a>';
	}
	return $plugin_meta;
}
add_filter( 'plugin_row_meta', 'wp_tag_order_add_github_link', 10, 4 );

if ( wp_tag_order_php_version_check() ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tag-updater.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/category-template.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/index.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/rest-api.php';
}
