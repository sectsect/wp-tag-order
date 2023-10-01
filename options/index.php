<div class="wrap">
	<h1>WP Tag Order</h1>
	<div id="setting-apply-settings_updated" class="updated settings-error notice is-dismissible" style="display: none;"></div>
	<section>
		<form method="post" action="options.php">
			<hr />
			<h3><?php _e( 'General Settings', 'wpto' ); ?></h3>
			<?php
			settings_fields( 'wpto-settings-group' );
			do_settings_sections( 'wpto-settings-group' );
			?>

			<fieldset>
				<legend style="display: block; margin-bottom: 10px;"><?php _e( 'Enable for these taxonomies', 'wpto' ); ?></legend>

				<?php
				$taxonomies = wto_get_non_hierarchical_taxonomies();

				if ( ! empty( $taxonomies ) ) :
					$enabled_taxonomies = wto_get_enabled_taxonomies();

					foreach ( $taxonomies as $taxonomy ) :
						if ( in_array( $taxonomy->name, $exclude, true ) ) {
							continue;
						}
						$is_checked = in_array( $taxonomy->name, $enabled_taxonomies, true );
						?>
					<div style="margin-top: 5px;">
						<input type="checkbox" id="<?php echo $taxonomy->name; ?>" name="wpto_enabled_taxonomies[]" value="<?php echo $taxonomy->name; ?>" <?php if ( $is_checked ) : ?>checked<?php endif; ?> />
						<label for="<?php echo $taxonomy->name; ?>">
							<?php echo $taxonomy->label; ?>
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
		<h3><?php _e( 'Advance Settings', 'wpto' ); ?></h3>
		<form method="post" action="">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" style="width: 300px;">
							<label for="apply" style="font-size: 14px; margin: 0;">Apply to all existing posts</label>
							<p style="font-size: 10px;margin: 5px 0 0;">
								This batch process respects the taxonomy specification in the general settings.
							</p>
							<p style="font-size: 10px;color: #999;margin: 5px 0 0;">
								⚠️ Please be sure to backup your database before running.<br>
								This simple process may takes a few minutes and ensures that your website can be rolled back quickly and safely if any issues arise.
							</p>
						</th>
						<td style="vertical-align: top;">
							<input name="apply" id="apply" class="button button-primary" type="submit" value="Apply">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</section>
</div>
