<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

function ghhs_found_pets_stylesheet() {

	wp_register_style('single_animal_style', plugins_url(plugin_basename(__DIR__)) . '/css/single-animal.css');
	wp_enqueue_style('single-animal');

	if (file_exists(plugins_url(plugin_basename(__DIR__)) . '/css/archive-animal.css')) {
		printf('<h2>fuck achrives</h2>');
	} else {
		printf('<h2>not fuck achrives</h2>');
	}

	wp_register_style('archive_animal_style', plugins_url(plugin_basename(__DIR__)) . '/css/archive-animal.css');
	wp_enqueue_style('archive-animal');

	wp_register_style('ghhs_found_pets_styles', plugins_url(plugin_basename(__DIR__)) . '/css/style.css');
	wp_enqueue_style('ghhs_found_pets_styles');

	wp_register_style('featherlight_style', plugins_url(plugin_basename(__DIR__)) . '/css/featherlight.min.css');
	wp_enqueue_style('featherlight_style');

	wp_register_style('featherlight_gallery_style', plugins_url(plugin_basename(__DIR__)) . '/css/featherlight.gallery.min.css');
	wp_enqueue_style('featherlight_gallery_style');

	wp_register_style('swiper_style', 'https://unpkg.com/swiper/swiper-bundle.min.css');
	wp_enqueue_style('swiper_style');

};
add_action('wp_print_styles', 'ghhs_found_pets_stylesheet');

function ghhs_found_pets_scripts() {

	//wp_register_script('latest_jquery', '//code.jquery.com/jquery-latest.js', true);
	//wp_enqueue_script('latest_jquery');

	wp_register_script('featherlight_script', plugins_url(plugin_basename(__DIR__)) . '/js/featherlight.min.js', array('jquery'), '', true);
	wp_enqueue_script('featherlight_script');

	wp_register_script('featherlight_gallery_script', plugins_url(plugin_basename(__DIR__)) . '/js/featherlight.gallery.min.js', array('jquery'), '', true);
	wp_enqueue_script('featherlight_gallery_script');

	wp_register_script('swipe_detect', '//cdnjs.cloudflare.com/ajax/libs/detect_swipe/2.1.1/jquery.detect_swipe.min.js', array('jquery'), '', true);
	wp_enqueue_script('swipe_detect');

	wp_register_script('swiper_script', 'https://unpkg.com/swiper/swiper-bundle.min.js');
	wp_enqueue_script('swiper_script');

};
add_action('wp_print_scripts', 'ghhs_found_pets_scripts');
