<?php
/**
 * For Options Page.
 *
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

global $wpdb;

/**
 * Add meta-box. @ https://www.sitepoint.com/adding-custom-meta-boxes-to-wordpress/
 *
 * @param  array $obj "description".
 * @param  array $metabox "description".
 *
 * @return void "description".
 */
function wpto_meta_box_markup( $obj, $metabox ) {
	wp_nonce_field( basename( __FILE__ ), 'wpto-meta-box-nonce' );
	?>
<div class="inner">
	<ul>
	<?php
	$taxonomy   = $metabox['args']['taxonomy'];
	$tags_value = get_post_meta( $obj->ID, 'wp-tag-order-' . $taxonomy, true );
	$tags       = array();
	$tags       = unserialize( $tags_value );
	if ( ! wto_is_array_empty( $tags ) ) :
		foreach ( $tags as $tagid ) :
			$tag = get_term_by( 'id', $tagid, $taxonomy );
			?>
		<li>
			<input type="text" readonly="readonly" value="<?php echo $tag->name; ?>">
			<input type="hidden" name="wp-tag-order-<?php echo $taxonomy; ?>[]" value="<?php echo $tag->term_id; ?>">
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
 * Add meta-box. @ https://www.sitepoint.com/adding-custom-meta-boxes-to-wordpress/
 *
 * @return void "description".
 */
function add_wpto_meta_box() {
	$screens = wto_has_tag_posttype();
	foreach ( $screens as $screen ) {
		$taxonomies = wto_get_enabled_taxonomies();
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( ! is_taxonomy_hierarchical( $taxonomy ) && 'post_format' !== $taxonomy ) {
					$obj   = get_taxonomy( $taxonomy );
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
 * Add classes to meta-box.
 *
 * @param array $classes "description".
 *
 * @return array "description".
 */
function add_metabox_classes_tagsdiv( $classes ) {
	$classes[] = 'wpto_meta_box';
	$classes[] = 'wpto_meta_box_tagsdiv';
	// add support for controller in metaboxes on WordPress 5.5 or higher.
	if ( ! wto_has_reorder_controller_in_metaboxes() ) {
		$classes[] = 'wpto_meta_box_no_reorder_controller';
	}

	return $classes;
}

/**
 * Add classes to meta-box.
 *
 * @param array $classes "description".
 *
 * @return array "description".
 */
function add_metabox_classes_panel( $classes ) {
	$classes[] = 'wpto_meta_box';
	$classes[] = 'wpto_meta_box_panel';
	// add support for controller in metaboxes on WordPress 5.5 or higher.
	if ( ! wto_has_reorder_controller_in_metaboxes() ) {
		$classes[] = 'wpto_meta_box_no_reorder_controller';
	}

	return $classes;
}

/**
 * Save meta box.
 *
 * @param string $post_id "description".
 * @param int    $post "description".
 * @param string $update Optional. After tags.
 *
 * @return statement "description".
 */
function save_wpto_meta_box( $post_id, $post, $update ) {
	if ( ! isset( $_POST['wpto-meta-box-nonce'] ) || ! wp_verify_nonce( $_POST['wpto-meta-box-nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	$pt = wto_has_tag_posttype();
	if ( ! in_array( $post->post_type, $pt, true ) ) {
		return $post_id;
	}

	$taxonomies = get_object_taxonomies( $post->post_type );
	if ( ! empty( $taxonomies ) ) {
		foreach ( $taxonomies as $taxonomy ) {
			if ( ! is_taxonomy_hierarchical( $taxonomy ) && wto_is_enabled_taxonomy( $taxonomy ) ) {
				$meta_box_tags_value = '';
				$fieldname           = 'wp-tag-order-' . $taxonomy;
				if ( isset( $_POST[ $fieldname ] ) ) {
					$meta_box_tags_value = serialize( $_POST[ $fieldname ] );
				}
				update_post_meta( $post_id, $fieldname, $meta_box_tags_value );
			}
		}
	}
}
add_action( 'save_post', 'save_wpto_meta_box', 10, 3 );

/**
 * Get the Plugin data.
 *
 * @return array "description".
 */
function wpto_get_plugin_data() {
	$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . 'wp-tag-order.php' );
	return $plugin_data;
}

/**
 * Load admin scripts.
 *
 * @param string $hook "description".
 *
 * @return void "description".
 */
function load_wpto_admin_script( $hook ) {
	$plugin_data    = wpto_get_plugin_data();
	$plugin_version = $plugin_data['Version'];
	global $post;
	if ( 'post-new.php' === $hook || 'post.php' === $hook ) {
		$pt                  = wto_has_tag_posttype();
		$taxonomies_attached = get_object_taxonomies( $post->post_type );
		if ( in_array( $post->post_type, $pt, true ) && wto_has_enabled_taxonomy( $taxonomies_attached ) ) {
			wp_enqueue_style( 'wto-style', plugin_dir_url( __DIR__ ) . 'assets/css/admin.css?v=' . $plugin_version, array() );
			wp_enqueue_script( 'wto-commons', plugin_dir_url( __DIR__ ) . 'assets/js/commons.js?v=' . $plugin_version, array( 'jquery' ), null, true );
			wp_enqueue_script( 'wto-script', plugin_dir_url( __DIR__ ) . 'assets/js/post.js?v=' . $plugin_version, array( 'wto-commons' ), null, true );
			$post_id       = ( isset( $_GET['post'] ) ) ? wp_unslash( $_GET['post'] ) : null;
			$action_sync   = 'wto_sync_tags';
			$action_update = 'wto_update_tags';
			wp_localize_script(
				'wto-script',
				'wto_data',
				array(
					'post_id'       => $post_id,
					'nonce_sync'    => wp_create_nonce( $action_sync ),
					'action_sync'   => $action_sync,
					'nonce_update'  => wp_create_nonce( $action_update ),
					'action_update' => $action_update,
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
				)
			);
		}
	}
}
add_action( 'admin_enqueue_scripts', 'load_wpto_admin_script', 10, 1 );

/**
 * Handling for Ajax Request.
 *
 * @return void "description".
 */
function ajax_wto_sync_tags() {
	$id       = $_POST['id'];
	$nonce    = $_POST['nonce'];
	$action   = $_POST['action'];
	$taxonomy = $_POST['taxonomy'];
	$tags     = $_POST['tags'];

	if ( ! isset( $nonce ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, $action ) || ! check_ajax_referer( $action, 'nonce', false ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}

	if ( $tags ) {
		$newtags    = explode( ',', wp_unslash( $tags ) );
		$newtagsids = array();
		foreach ( $newtags as $newtag ) {
			$term = term_exists( $newtag, sanitize_text_field( wp_unslash( $taxonomy ) ) );
			if ( 0 === $term || null === $term ) {
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
					if ( ! in_array( $val, $basetagsids, true ) ) {
						array_push( $basetagsids, $val );
					} else {
						$key = array_search( $val, $basetagsids, true );
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
			$newtagsids_int    = array_map( 'intval', $newtagsids ); // Cast string to integer  @ Line: 23 !
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
	} else {
		delete_post_meta( sanitize_text_field( wp_unslash( $id ) ), 'wp-tag-order-' . sanitize_text_field( wp_unslash( $taxonomy ) ) );
		$return = '';
	}

	echo json_encode( $return );
	exit;
}
add_action( 'wp_ajax_wto_sync_tags', 'ajax_wto_sync_tags' );
add_action( 'wp_ajax_nopriv_wto_sync_tags', 'ajax_wto_sync_tags' );

/**
 * Handling for Ajax Request.
 *
 * @return void "description".
 */
function ajax_wto_update_tags() {
	$id       = $_POST['id'];
	$nonce    = $_POST['nonce'];
	$action   = $_POST['action'];
	$taxonomy = $_POST['taxonomy'];
	$tags     = $_POST['tags'];

	if ( ! isset( $tags ) || ! isset( $nonce ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, $action ) || ! check_ajax_referer( $action, 'nonce', false ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
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

	echo $return;
	exit;
}
add_action( 'wp_ajax_wto_update_tags', 'ajax_wto_update_tags' );
add_action( 'wp_ajax_nopriv_wto_update_tags', 'ajax_wto_update_tags' );

/**
 * Add Options Page.
 *
 * @return void "description".
 */
function wpto_menu() {
	$page_hook_suffix = add_options_page( 'WP Tag Order', 'WP Tag Order', 'manage_options', 'wpto_menu', 'wpto_options_page' );
	add_action( 'admin_print_styles-' . $page_hook_suffix, 'wpto_admin_styles' );
	add_action( 'admin_print_scripts-' . $page_hook_suffix, 'wpto_admin_scripts' );
	add_action( 'admin_init', 'register_wpto_settings' );
}
add_action( 'admin_menu', 'wpto_menu' );

/**
 * Load admin styles.
 *
 * @return void "description".
 */
function wpto_admin_styles() {
	$plugin_data    = wpto_get_plugin_data();
	$plugin_version = $plugin_data['Version'];
	wp_enqueue_style( 'sweetalert2', plugin_dir_url( __DIR__ ) . 'assets/css/options.css?v=' . $plugin_version, array() );
}

/**
 * Load admin scripts.
 *
 * @return void "description".
 */
function wpto_admin_scripts() {
	$plugin_data    = wpto_get_plugin_data();
	$plugin_version = $plugin_data['Version'];
	wp_enqueue_script( 'wto-commons', plugin_dir_url( __DIR__ ) . 'assets/js/commons.js?v=' . $plugin_version, array( 'jquery' ), null, true );
	wp_enqueue_script( 'wto-options-script', plugin_dir_url( __DIR__ ) . 'assets/js/options.js?v=' . $plugin_version, array( 'wto-commons' ), null, true );
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
 * Handling for Ajax Request.
 *
 * @return void "description".
 */
function ajax_wto_options() {
	$nonce  = $_POST['nonce'];
	$action = $_POST['action'];
	if ( ! isset( $nonce ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, $action ) || ! check_ajax_referer( $action, 'nonce', false ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
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
						$meta  = get_post_meta( $postid, 'wp-tag-order-' . $taxonomy, true );
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

	echo json_encode( $return );
	exit;
}
add_action( 'wp_ajax_wto_options', 'ajax_wto_options' );
add_action( 'wp_ajax_nopriv_wto_options', 'ajax_wto_options' );

/**
 * Register settings.
 *
 * @return void "description".
 */
function register_wpto_settings() {
	register_setting( 'wpto-settings-group', 'wpto_enabled_taxonomies' );
}

/**
 * Load file for Options Page.
 *
 * @return void "description".
 */
function wpto_options_page() {
	require_once plugin_dir_path( __DIR__ ) . 'options/index.php';
}
