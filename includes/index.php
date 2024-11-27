<?php
/**
 * For Options Page.
 *
 * @since 1.0.0
 *
 * @package WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 */

declare(strict_types=1);

/**
 * Template for category.
 *
 * @package WP_Tag_Order
 * @subpackage WP_Tag_Order/includes
 */
global $wpdb;

/**
 * Adds a meta box for tag ordering on post edit screens.
 * This function creates a meta box that allows users to order tags associated with a post.
 *
 * @param WP_Post              $obj The post object currently being edited.
 * @param array<string, mixed> $metabox The meta box arguments including 'taxonomy' to specify which taxonomy the tags belong to.
 * @return void
 */
function wpto_meta_box_markup( WP_Post $obj, array $metabox ): void {
	wp_nonce_field( basename( __FILE__ ), 'wpto-meta-box-nonce' );
	?>
<div class="inner">
	<ul>
	<?php
	$taxonomy   = isset( $metabox['args'] ) && is_array( $metabox['args'] ) && isset( $metabox['args']['taxonomy'] )
		? wpto_cast_mixed_to_string( $metabox['args']['taxonomy'] )
		: '';
	$meta_key   = 'wp-tag-order-' . $taxonomy;
	$tags_value = get_post_meta( $obj->ID, 'wp-tag-order-' . $taxonomy, true );
	$tags       = is_string( $tags_value ) ? unserialize( $tags_value ) : array();
	if ( $tags && is_array( $tags ) ) :
		foreach ( $tags as $tagid ) :
			$tagid = wpto_cast_mixed_to_int( $tagid );
			$tag   = ! empty( $taxonomy ) ? get_term_by( 'id', $tagid, $taxonomy ) : null;
			if ( ! $tag instanceof WP_Term ) {
				continue; // Skip if $tag is not a WP_Term object.
			}
			$hidden_name = 'wp-tag-order-' . $taxonomy . '[]';
			?>
		<li>
			<input type="text" readonly="readonly" value="<?php echo esc_attr( $tag->name ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $hidden_name ); ?>" value="<?php echo esc_attr( wpto_cast_mixed_to_string( $tag->term_id ) ); ?>">
		</li>
			<?php
		endforeach;
	endif;
	?>
	</ul>
</div>
	<?php
}

/**
 * Registers and adds meta boxes for tag ordering to applicable post types.
 * This function iterates through post types and taxonomies to add a custom meta box for non-hierarchical taxonomies.
 *
 * @return void
 */
function add_wpto_meta_box(): void {
	$screens = wto_has_tag_posttype();
	foreach ( $screens as $screen ) {
		$taxonomies = wto_get_enabled_taxonomies();
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( ! is_taxonomy_hierarchical( $taxonomy ) && 'post_format' !== $taxonomy ) {
					$obj = get_taxonomy( $taxonomy );
					if ( ! $obj instanceof WP_Taxonomy ) {
						continue; // Skip if $obj is not a WP_Taxonomy object.
					}
					$label = $obj->label;
					add_meta_box(
						'wpto_meta_box-' . $taxonomy,
						__( 'Tag Order - ', 'wp-tag-order' ) . $label,
						'wpto_meta_box_markup',
						$screen,
						'side',
						'core',
						array(
							'taxonomy' => $taxonomy,
						)
					);
					add_filter( "postbox_classes_{$screen}_tagsdiv-{$taxonomy}", 'add_metabox_classes_tagsdiv' );
					add_filter( "postbox_classes_{$screen}_wpto_meta_box-{$taxonomy}", 'add_metabox_classes_panel' );
				}
			}
		}
	}
}
add_action( 'add_meta_boxes', 'add_wpto_meta_box' );

/**
 * Adds CSS classes to the tags meta box to enhance its appearance and functionality.
 * This function appends additional CSS classes to the meta box for styling and JavaScript interactions.
 *
 * @param array<string> $classes The existing CSS classes for the meta box.
 * @return array<string> The modified list of CSS classes.
 */
function add_metabox_classes_tagsdiv( array $classes ): array {
	$classes[] = 'wpto_meta_box';
	$classes[] = 'wpto_meta_box_tagsdiv';
	// add support for controller in metaboxes on WordPress 5.5 or higher.
	if ( ! wto_has_reorder_controller_in_metaboxes() ) {
		$classes[] = 'wpto_meta_box_no_reorder_controller';
	}

	return $classes;
}

/**
 * Adds CSS classes to the tag order meta box panel.
 * Similar to `add_metabox_classes_tagsdiv`, this function enhances the meta box panel's CSS classes.
 *
 * @param array<string> $classes The existing CSS classes for the panel.
 * @return array<string> The modified list of CSS classes.
 */
function add_metabox_classes_panel( array $classes ): array {
	$classes[] = 'wpto_meta_box';
	$classes[] = 'wpto_meta_box_panel';
	// add support for controller in metaboxes on WordPress 5.5 or higher.
	if ( ! wto_has_reorder_controller_in_metaboxes() ) {
		$classes[] = 'wpto_meta_box_no_reorder_controller';
	}

	return $classes;
}

/**
 * Saves the ordered tags when a post is saved.
 * This function checks for user permissions, nonce validation, and autosave status before saving the tag order into post meta.
 *
 * @param int     $post_id The ID of the post being saved.
 * @param WP_Post $post The post object associated with the ID.
 * @param bool    $update Indicates if the save operation is for an existing post being updated.
 * @return void
 */
function save_wpto_meta_box( int $post_id, WP_Post $post, bool $update ): void {
	$nonce = filter_input( INPUT_POST, 'wpto-meta-box-nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, basename( __FILE__ ) ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! $post->post_type ) {
		return;
	}

	$post_type         = sanitize_key( $post->post_type );
	$post_type_has_tag = wto_has_tag_posttype();
	if ( ! in_array( $post_type, $post_type_has_tag, true ) ) {
		return;
	}

	$taxonomies = get_object_taxonomies( $post->post_type );

	if ( empty( $taxonomies ) ) {
		return;
	}

	foreach ( $taxonomies as $taxonomy ) {
		$taxonomy = sanitize_key( $taxonomy );
		if ( ! is_taxonomy_hierarchical( $taxonomy ) && wto_is_enabled_taxonomy( $taxonomy ) ) {
			$fieldname = 'wp-tag-order-' . $taxonomy;
			$tags      = filter_input( INPUT_POST, $fieldname, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

			$meta_box_tags_value = '';
			if ( $tags ) {
				$meta_box_tags_value = serialize( array_map( 'sanitize_text_field', $tags ) );
			}

			update_post_meta( $post_id, $fieldname, $meta_box_tags_value );
		}
	}
}
add_action( 'save_post', 'save_wpto_meta_box', 10, 3 );

/**
 * Retrieves plugin data from the main plugin file.
 * This function fetches data such as version number and plugin name from the plugin's main file.
 *
 * @return array<string, mixed> The plugin data.
 */
function wpto_get_plugin_data(): array {
	$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . 'wp-tag-order.php' );
	return $plugin_data;
}

/**
 * Enqueues admin-specific styles and scripts for the plugin.
 * This function loads necessary CSS and JavaScript files for the plugin's admin interface on applicable admin pages.
 *
 * @param string $hook The current admin page hook suffix.
 * @throws Exception If an error occurs during script processing or localization.
 * @return void
 */
function load_wpto_admin_script( string $hook ): void {
	$plugin_data    = wpto_get_plugin_data();
	$plugin_version = wpto_cast_mixed_to_string( $plugin_data['Version'] );
	global $post;

	// Early return for unsupported scenarios.
	if ( ! $post?->post_type || ! in_array( $hook, array( 'post-new.php', 'post.php' ), true ) ) {
		return;
	}

	$pt = wto_has_tag_posttype();

	// Early validation and error handling.
	if ( ! in_array( $post->post_type, $pt, true ) ) {
		wp_die(
			esc_html(
				sprintf(
				/* translators: %s: Post type name */
					__( 'Post type "%s" is not supported by WP Tag Order.', 'wp-tag-order' ),
					esc_html( $post->post_type )
				)
			),
			esc_html__( 'WP Tag Order Error', 'wp-tag-order' ),
			array( 'response' => 403 )
		);
	}

	$taxonomies_attached = get_object_taxonomies( $post->post_type );

	if ( ! wto_has_enabled_taxonomy( $taxonomies_attached ) ) {
		wp_die(
			esc_html(
				sprintf(
				/* translators: %s: Post type name */
					__( 'No enabled taxonomies found for post type "%s".', 'wp-tag-order' ),
					esc_html( $post->post_type )
				)
			),
			esc_html__( 'WP Tag Order Error', 'wp-tag-order' ),
			array( 'response' => 403 )
		);
	}

	wp_enqueue_style( 'wto-style', plugin_dir_url( __DIR__ ) . 'assets/css/admin.css', array(), $plugin_version );
	wp_enqueue_script( 'wto-script', plugin_dir_url( __DIR__ ) . 'assets/js/post.js', array( 'jquery' ), $plugin_version, true );

	$post_id  = null;
	$get_post = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	try {
		if ( $get_post ) {
			// Validate post ID.
			$post_id = filter_var( $get_post, FILTER_VALIDATE_INT );
			if ( false === $post_id ) {
				wp_die(
					esc_html__( 'Invalid post ID detected.', 'wp-tag-order' ),
					esc_html__( 'WP Tag Order Error', 'wp-tag-order' ),
					array( 'response' => 400 )
				);
			}
		}

		$action_sync   = 'wto_sync_tags';
		$action_update = 'wto_update_tags';
		wp_localize_script(
			'wto-script',
			'wto_data',
			array(
				'post_id'       => $post_id ?? 0,
				'nonce_sync'    => wp_create_nonce( $action_sync ),
				'action_sync'   => $action_sync,
				'nonce_update'  => wp_create_nonce( $action_update ),
				'action_update' => $action_update,
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
			)
		);
	} catch ( Exception $e ) {
		wp_die(
			esc_html(
				sprintf(
				/* translators: %s: Error message */
					__( 'WP Tag Order encountered an error: %s', 'wp-tag-order' ),
					esc_html( $e->getMessage() )
				)
			),
			esc_html__( 'WP Tag Order Error', 'wp-tag-order' ),
			array( 'response' => 400 )
		);
	}
}
add_action( 'admin_enqueue_scripts', 'load_wpto_admin_script', 10, 1 );

/**
 * Handles AJAX request for synchronizing tags.
 * This function processes AJAX requests to synchronize tag order changes made on the client side with the server.
 *
 * @return void
 */
function ajax_wto_sync_tags(): void {
	$id       = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$nonce    = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$action   = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$taxonomy = filter_input( INPUT_POST, 'taxonomy', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$tags     = isset( $_POST['tags'] ) && is_string( $_POST['tags'] )
		? sanitize_text_field( wp_unslash( $_POST['tags'] ) )
		: '';

	if (
		! $id ||
		! $nonce ||
		! $action ||
		! wp_verify_nonce( $nonce, $action ) ||
		! check_ajax_referer( (string) $action, 'nonce', false ) ||
		! isset( $_SERVER['REQUEST_METHOD'] ) ||
		'POST' !== $_SERVER['REQUEST_METHOD']
	) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}

	$meta_box_tags_value = '';

	if ( $tags && $taxonomy ) {
		$newtags    = explode( ',', $tags );
		$newtagsids = array();

		foreach ( $newtags as $newtag ) {
			$term = term_exists( $newtag, $taxonomy );
			if ( null === $term ) {
				$term_taxonomy_ids = wp_set_object_terms( absint( $id ), $newtag, $taxonomy, true );
				if ( is_wp_error( $term_taxonomy_ids ) ) {
					exit;
				}
			}
			$tag = get_term_by( 'name', $newtag, $taxonomy );
			if ( ! $tag instanceof WP_Term ) {
				continue;
			}
			$newtagsids[] = (string) $tag->term_id;
		}

		$savedata = array();
		$tags_val = get_post_meta( absint( $id ), 'wp-tag-order-' . $taxonomy, true );

		if ( $tags_val ) {
			$basetagsids = is_string( $tags_val ) ? unserialize( $tags_val ) : array();
			if ( is_array( $basetagsids ) ) {
				$added = wto_array_diff_interactive( $newtagsids, $basetagsids );
				foreach ( $added as $val ) {
					if ( ! in_array( $val, $basetagsids, true ) ) {
						$basetagsids[] = $val;
					} else {
						$key = array_search( $val, $basetagsids, true );
						if ( false !== $key ) {
							unset( $basetagsids[ $key ] );
						}
					}
				}
				$savedata = $basetagsids;
			}
		} else {
			$savedata = $newtagsids;
		}

		$meta_box_tags_value = serialize( $savedata );
		update_post_meta( absint( $id ), 'wp-tag-order-' . $taxonomy, $meta_box_tags_value );

		$newtagsids_int    = array_map( 'intval', $newtagsids );
		$term_taxonomy_ids = wp_set_object_terms( absint( $id ), $newtagsids_int, $taxonomy );
		if ( is_wp_error( $term_taxonomy_ids ) ) {
			exit;
		}

		$return = '';
		if ( ! wto_is_array_empty( $savedata ) ) {
			foreach ( $savedata as $newtag ) {
				$tag = get_term_by( 'id', (int) $newtag, $taxonomy );
				if ( ! $tag instanceof WP_Term ) {
					continue;
				}
				$return .= '<li><input type="text" readonly="readonly" value="' . esc_attr( $tag->name ) . '"><input type="hidden" name="wp-tag-order-' . esc_attr( $taxonomy ) . '[]" value="' . esc_attr( (string) $tag->term_id ) . '"></li>';
			}
		}
	} else {
		delete_post_meta( absint( $id ), 'wp-tag-order-' . $taxonomy );
		$return = '';
	}

	echo wp_json_encode( $return );
	exit;
}
add_action( 'wp_ajax_wto_sync_tags', 'ajax_wto_sync_tags' );
add_action( 'wp_ajax_nopriv_wto_sync_tags', 'ajax_wto_sync_tags' );

/**
 * Handles AJAX request for updating tag orders.
 * This function updates the tag order based on user input from the AJAX request.
 *
 * @return void Outputs true on success or false on failure.
 */
function ajax_wto_update_tags(): void {
	$id       = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$nonce    = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$action   = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$taxonomy = filter_input( INPUT_POST, 'taxonomy', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$tags     = filter_input( INPUT_POST, 'tags', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	if (
		empty( $id ) ||
		empty( $nonce ) ||
		empty( $action ) ||
		empty( $taxonomy ) ||
		empty( $tags ) ||
		! wp_verify_nonce( $nonce, $action ) ||
		! check_ajax_referer( $action, 'nonce', false ) ||
		! isset( $_SERVER['REQUEST_METHOD'] ) ||
		'POST' !== $_SERVER['REQUEST_METHOD']
	) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}

	try {
		$tag_updater = new \WP_Tag_Order\Tag_Updater();
		$result      = $tag_updater->update_tag_order(
			intval( $id ),
			$taxonomy,
			$tags
		);

		wp_send_json_success( $result );
	} catch ( \InvalidArgumentException $e ) {
		wp_send_json_error( $e->getMessage(), 400 );
	}
}
add_action( 'wp_ajax_wto_update_tags', 'ajax_wto_update_tags' );
add_action( 'wp_ajax_nopriv_wto_update_tags', 'ajax_wto_update_tags' );

/**
 * Adds the plugin options page to the WordPress admin menu.
 * This function creates a new options page under the WordPress settings menu for configuring the plugin.
 *
 * @return void
 */
function wpto_menu(): void {
	$page_hook_suffix = add_options_page( 'WP Tag Order', 'WP Tag Order', 'manage_options', 'wpto_menu', 'wpto_options_page' );
	add_action( 'admin_print_styles-' . $page_hook_suffix, 'wpto_admin_styles' );
	add_action( 'admin_print_scripts-' . $page_hook_suffix, 'wpto_admin_scripts' );
	add_action( 'admin_init', 'register_wpto_settings' );
}
add_action( 'admin_menu', 'wpto_menu' );

/**
 * Enqueues the admin styles for the plugin options page.
 *
 * @return void
 */
function wpto_admin_styles(): void {
	$plugin_data    = wpto_get_plugin_data();
	$plugin_version = $plugin_data['Version'];
	$version        = is_string( $plugin_version ) ? $plugin_version : '';
	wp_enqueue_style( 'sweetalert2', plugin_dir_url( __DIR__ ) . 'assets/css/options.css', array(), $version );
}

/**
 * Enqueues the admin scripts for the plugin options page.
 *
 * @return void
 */
function wpto_admin_scripts(): void {
	$plugin_data    = wpto_get_plugin_data();
	$plugin_version = $plugin_data['Version'];
	$version        = is_string( $plugin_version ) ? $plugin_version : '';
	// wp_enqueue_script( 'wto-commons', plugin_dir_url( __DIR__ ) . 'assets/js/commons.js?v=' . $plugin_version, array( 'jquery' ), null, true ); // phpcs:ignore.
	wp_enqueue_script( 'wto-options-script', plugin_dir_url( __DIR__ ) . 'assets/js/options.js', array( 'jquery' ), $version, true );
	$action = 'wto_options';
	wp_localize_script(
		'wto-options-script',
		'wto_options_data',
		array(
			'nonce'    => wp_create_nonce( $action ),
			'action'   => $action,
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		)
	);
}

/**
 * Handles the AJAX request for updating tag order options.
 *
 * @return void
 */
function ajax_wto_options(): void {
	$nonce  = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	if (
		empty( $nonce ) ||
		empty( $action ) ||
		! wp_verify_nonce( $nonce, $action ) ||
		! check_ajax_referer( $action, 'nonce', false ) ||
		! isset( $_SERVER['REQUEST_METHOD'] ) ||
		'POST' !== $_SERVER['REQUEST_METHOD']
	) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}

	$count = 0;
	$pts   = wto_has_tag_posttype();
	foreach ( $pts as $pt ) {
		global $post;
		$ids      = array();
		$my_query = new WP_Query();
		$param    = array(
			'post_type'      => $pt,
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => array( 'any', 'trash', 'auto-draft' ),
		);
		$my_query->query( $param );
		if ( $my_query->have_posts() ) :
			while ( $my_query->have_posts() ) :
				$my_query->the_post();
				array_push( $ids, $post->ID );
			endwhile;
		endif;
		wp_reset_postdata();

		$taxonomies = get_object_taxonomies( $pt );
		if ( ! empty( $taxonomies ) ) {
			foreach ( $ids as $postid ) {
				foreach ( $taxonomies as $taxonomy ) {
					if ( ! is_taxonomy_hierarchical( $taxonomy ) && 'post_format' !== $taxonomy && wto_has_enabled_taxonomy( $taxonomies ) ) {
						$terms = get_the_terms( $postid, $taxonomy );
						if ( is_wp_error( $terms ) || false === $terms ) {
							continue; // Skip if $terms is a WP_Error or false.
						}
						$meta = get_post_meta( $postid, 'wp-tag-order-' . $taxonomy, true );
						if ( ! empty( $terms ) && ! $meta ) {
							$term_ids = array();
							foreach ( $terms as $term ) {
								array_push( $term_ids, $term->term_id );
							}
							$meta_box_tags_value = serialize( $term_ids );
							$return              = update_post_meta( $postid, 'wp-tag-order-' . $taxonomy, $meta_box_tags_value );
							if ( $return ) {
								++$count;
							}
						}
					}
				}
			}
		}
	}
	$return = array( 'count' => $count );

	echo wp_json_encode( $return );
	exit;
}
add_action( 'wp_ajax_wto_options', 'ajax_wto_options' );
add_action( 'wp_ajax_nopriv_wto_options', 'ajax_wto_options' );

/**
 * Registers the plugin settings.
 * This function registers settings that can be configured from the plugin's options page.
 *
 * @return void
 */
function register_wpto_settings(): void {
	register_setting( 'wpto-settings-group', 'wpto_enabled_taxonomies' );
}

/**
 * Loads the options page template.
 * This function includes the PHP file that contains the HTML and PHP code for the plugin's options page.
 *
 * @return void
 */
function wpto_options_page(): void {
	require_once plugin_dir_path( __DIR__ ) . 'options/index.php';
}
