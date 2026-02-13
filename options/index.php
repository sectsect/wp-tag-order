<?php
/**
 * Renders the template for the category settings page.
 *
 * @since 1.0.0
 *
 * @package WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
	<h1>WP Tag Order</h1>
	<div id="setting-apply-settings_updated" class="updated settings-error notice is-dismissible" style="display: none;"></div>
	<section>
		<form method="post" action="options.php">
			<hr />
			<h3><?php esc_html_e( 'General Settings', 'wp-tag-order' ); ?></h3>
			<?php
			settings_fields( 'wpto-settings-group' );
			do_settings_sections( 'wpto-settings-group' );
			?>

			<fieldset>
				<legend style="display: block; margin-bottom: 10px;"><?php esc_html_e( 'Enable for these taxonomies', 'wp-tag-order' ); ?></legend>

				<?php
				$taxonomies = wp_tag_order_get_non_hierarchical_taxonomies();

				if ( ! empty( $taxonomies ) ) :
					$enabled_taxonomies = wp_tag_order_get_enabled_taxonomies();

					foreach ( $taxonomies as $taxonomy ) :
						$is_checked = in_array( $taxonomy->name, (array) $enabled_taxonomies, true );
						?>
					<div style="margin-top: 5px;">
						<input type="checkbox" id="<?php echo esc_attr( $taxonomy->name ); ?>" name="<?php echo esc_attr( WP_TAG_ORDER_OPTION_ENABLED_TAXONOMIES ); ?>[]" value="<?php echo esc_attr( $taxonomy->name ); ?>" <?php checked( $is_checked ); ?> />
						<label for="<?php echo esc_attr( $taxonomy->name ); ?>">
							<?php echo esc_html( $taxonomy->label ); ?>
						</label>
					</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</fieldset>

			<?php submit_button(); ?>
		</form>
	</section>
	<section>
		<hr />
		<h3><?php esc_html_e( 'Advance Settings', 'wp-tag-order' ); ?></h3>
		<form method="post" action="">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" style="width: 300px;">
							<label for="apply" style="font-size: 14px; margin: 0;"><?php esc_html_e( 'Apply to all existing posts', 'wp-tag-order' ); ?></label>
							<p style="font-size: 10px;margin: 5px 0 0;">
								<?php esc_html_e( 'This batch process respects the taxonomy specification in the general settings.', 'wp-tag-order' ); ?>
							</p>
							<p style="font-size: 10px;color: #999;margin: 5px 0 0;">
								<?php esc_html_e( '⚠️ Please be sure to backup your database before running.', 'wp-tag-order' ); ?><br>
								<?php esc_html_e( 'This simple process may takes a few minutes and ensures that your website can be rolled back quickly and safely if any issues arise.', 'wp-tag-order' ); ?>
							</p>
						</th>
						<td style="vertical-align: top;">
							<input name="apply" id="apply" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Apply', 'wp-tag-order' ); ?>">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</section>
</div>
