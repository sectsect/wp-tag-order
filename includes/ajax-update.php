<?php
	require('../../../../wp-load.php');
	require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions.php';

	extract($_POST, EXTR_SKIP);
	if(!isset($tags) || !isset($nonce) || empty($nonce) || !wp_verify_nonce($nonce, 'wpto') || $_SERVER["REQUEST_METHOD"] != "POST"){
		wp_safe_redirect(home_url('/'), 301);
		exit;
	}

	if($id){
		// new data
		$newordertags = explode(",", sanitize_text_field(wp_unslash($tags)));			// strings to array
	//	$newordertags_str = array_map('strval', $newordertags);		// Cast integer to string
		if(isset($newordertags)){
	        $meta_box_tags_value = serialize($newordertags);
	    }
	    $return = update_post_meta(sanitize_text_field(wp_unslash($id)), "wp-tag-order-" . sanitize_text_field(wp_unslash($taxonomy)), $meta_box_tags_value);
	}else{
		$return = false;
	}

	echo json_encode($return);
	exit;
