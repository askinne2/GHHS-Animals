<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

function ghhs_found_pets_stylesheet() {
	wp_register_style('bootstrap_styles', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css');
	wp_enqueue_style('bootstrap_styles');

	wp_register_style('single_animal_style', plugins_url(plugin_basename(__DIR__)) . '/css/single-animal-style.css');
	wp_enqueue_style('single-animal_style');

	wp_register_style('archive_animal_style', plugins_url(plugin_basename(__DIR__)) . '/css/archive-animal-style.css');
	wp_enqueue_style('archive-animal_style');

	wp_register_style('ghhs_found_pets_styles', plugins_url(plugin_basename(__DIR__)) . '/css/ghhs_found_pets.css');
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

	wp_register_script('bootstrap_js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.min.js');
	wp_enqueue_script('bootstrap_js');

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
