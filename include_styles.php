<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function add_my_plugin_stylesheet() {
	wp_register_style('ghhs_found_pets_styles', '/wp-content/plugins/ghhs_found_pets/css/style.css');
	wp_enqueue_style('ghhs_found_pets_styles');
	//wp_register_style('api_test2_materialize_styles', '/wp-content/plugins/api_test2.php/css/materialize.min.css');
	/*
	wp_register_style('swiper_style', 'https://unpkg.com/swiper/swiper-bundle.min.css');
	wp_enqueue_style('swiper_style');
	*/

	wp_register_style('featherlight_style', '//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.css');
	wp_enqueue_style('featherlight_style');

	wp_register_style('featherlight_gallery_style', '//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.gallery.min.css');
	wp_enqueue_style('featherlight_gallery_style');
	

	
};
add_action( 'wp_print_styles', 'add_my_plugin_stylesheet' );


	
function add_my_plugin_scripts() {
	/*
	wp_register_script('swiper_script', 'https://unpkg.com/swiper/swiper-bundle.min.js');
	wp_enqueue_script('swiper_script');
	*/

	wp_register_script('latest_jquery', '//code.jquery.com/jquery-latest.js');
	wp_enqueue_script('latest_jquery');

	wp_register_script('featherlight_script', '//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.js');
	wp_enqueue_script('featherlight_script');

	wp_register_script('featherlight_gallery_script', '//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.gallery.min.js');
	wp_enqueue_script('featherlight_gallery_script');


	wp_register_script('swipe_detect', '//cdnjs.cloudflare.com/ajax/libs/detect_swipe/2.1.1/jquery.detect_swipe.min.js');
	wp_enqueue_script('swipe_detect');

	
	

	

};
add_action( 'wp_print_scripts', 'add_my_plugin_scripts' );

?>