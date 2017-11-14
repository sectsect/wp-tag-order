<?php
global $wpdb;

/*==================================================
	Add meta-box	@ https://www.sitepoint.com/adding-custom-meta-boxes-to-wordpress/
================================================== */
function wpto_meta_box_markup($object, $metabox) {
	wp_nonce_field( basename(__FILE__), "wpto-meta-box-nonce" );
?>
<div class="inner">
	<ul>
	<?php
		$taxonomy = $metabox['args']['taxonomy'];
	    $tags_value = get_post_meta( $object->ID, "wp-tag-order-" . $taxonomy, true );
		$tags = array();
		$tags = unserialize( $tags_value );
		if ( ! wto_is_array_empty( $tags ) ):
			foreach ( $tags as $tagid ):
				$tag = get_term_by( 'id', $tagid, $taxonomy );
	?>
		<li>
			<input type="text" readonly="readonly" value="<?php echo $tag->name; ?>">
			<input type="hidden" name="wp-tag-order-<?php echo $taxonomy; ?>[]" value="<?php echo $tag->term_id; ?>">
		</li>
	<?php endforeach; endif; ?>
	</ul>
</div>
<?php
}

function add_wpto_meta_box() {
	$screens = wto_has_tag_posttype();	// Get All Post-types that holds the non-hierarchical taxonomies
	foreach ( $screens as $screen ) {
		$taxonomies = get_object_taxonomies( $screen );
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
			    // Get taxonomy in Post-types that holds the non-hierarchical taxonomies
				if ( ! is_taxonomy_hierarchical( $taxonomy ) && $taxonomy !== "post_format" ) {
					$obj = get_taxonomy( $taxonomy );
					$label = $obj->label;
					add_meta_box (
						'wpto_meta_box-' . $taxonomy,						// id for metabox
						__('Tag Order - ' . $label, 'wp-tag-order'),		// label
						'wpto_meta_box_markup',								// callback function for output
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

function add_metabox_classes_tagsdiv( $classes ) {
	$classes[] = 'wpto_meta_box';
	$classes[] = 'wpto_meta_box_tagsdiv';

    return $classes;
}
function add_metabox_classes_panel( $classes ) {
	$classes[] = 'wpto_meta_box';
	$classes[] = 'wpto_meta_box_panel';

    return $classes;
}

function save_wpto_meta_box( $post_id, $post, $update ) {
    if ( ! isset($_POST["wpto-meta-box-nonce"]) || ! wp_verify_nonce( $_POST["wpto-meta-box-nonce"], basename(__FILE__) ) )
        return $post_id;

    if( ! current_user_can( "edit_post", $post_id ) )
        return $post_id;

    if( defined("DOING_AUTOSAVE") && DOING_AUTOSAVE )
        return $post_id;

    // $slug = "news";
    // if($slug != $post->post_type)
    //     return $post_id;
	$pt = wto_has_tag_posttype();
	if( ! in_array( $post->post_type, $pt ) ) {
	    return $post_id;
	}

	$taxonomies = get_object_taxonomies( $post->post_type );
	if( ! empty( $taxonomies ) ) {
		foreach ( $taxonomies as $taxonomy ) {
			// only want hierarchical -- no tags please
			if( ! is_taxonomy_hierarchical( $taxonomy ) ) {
				$meta_box_tags_value = "";
				$fieldname = "wp-tag-order-" . $taxonomy;
				if ( isset( $_POST[$fieldname] ) ) {
			        $meta_box_tags_value = serialize( $_POST[$fieldname] );
			    }
			    update_post_meta( $post_id, "$fieldname", $meta_box_tags_value );
			}
		}
	}
}
add_action( "save_post", "save_wpto_meta_box", 10, 3 );

/*==================================================
	css / js
================================================== */
function load_wpto_admin_script( $hook ) {
    global $post;
    if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
		$pt = wto_has_tag_posttype();
		if ( in_array( $post->post_type, $pt ) ) {
			wp_enqueue_style( 'wto-style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/admin.css', array() );
			// wp_enqueue_script( 'jquery-ui-sortable' );
    		wp_enqueue_script( 'wto-script', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/script.js', array() );
			$post_id = ( isset( $_GET['post'] )) ? wp_unslash( $_GET['post'] ) : null;
			wp_localize_script( 'wto-script', 'wto_data', array(
				'post_id'        => $post_id,
				'nonce'          => wp_create_nonce('wpto'),
				'plugin_dir_url' => plugin_dir_url( dirname( __FILE__ ) ),
		    ) );
        }
    }
}
add_action( 'admin_enqueue_scripts', 'load_wpto_admin_script', 10, 1 );

/*==================================================
    Add Options Page
================================================== */
add_action( 'admin_menu', 'wpto_menu' );
function wpto_menu() {
    $page_hook_suffix = add_options_page( 'WP Tag Order', 'WP Tag Order', 'manage_options', 'wpto_menu', 'wpto_options_page' );
    add_action( 'admin_print_styles-' . $page_hook_suffix, 'wpto_admin_styles' );
    add_action( 'admin_print_scripts-' . $page_hook_suffix, 'wpto_admin_scripts' );
    add_action( 'admin_init', 'register_wpto_settings' );
}
function wpto_admin_styles() {
    wp_enqueue_style( 'sweetalert2', '//cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/4.3.3/sweetalert2.min.css', array() );
}
function wpto_admin_scripts() {
	wp_enqueue_script( 'sweetalert2', '//cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/4.3.3/sweetalert2.min.js', array('jquery') );
	wp_enqueue_script( 'wto-options-script', plugin_dir_url( dirname(__FILE__) ) . 'options/js/script.js', array('sweetalert2') );
	wp_localize_script( 'wto-options-script', 'wto_options_data', array(
		'nonce'          => wp_create_nonce('wpto-options'),
		'plugin_dir_url' => plugin_dir_url(dirname(__FILE__)),
	) );
}
function register_wpto_settings() {

}
function wpto_options_page() {
    require_once plugin_dir_path( dirname(__FILE__) ) . 'options/index.php';
}
