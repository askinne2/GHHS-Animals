<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

function ghhs_found_pets_stylesheet() {
	wp_register_style('bootstrap_styles', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css');
	wp_enqueue_style('bootstrap_styles');

	wp_register_style('ghhs_found_pets_styles', plugins_url(plugin_basename(__DIR__)) . '/css/ghhs_found_pets.css');
	wp_enqueue_style('ghhs_found_pets_styles');

};
add_action('wp_print_styles', 'ghhs_found_pets_stylesheet');

function ghhs_found_pets_scripts() {

	wp_register_script('bootstrap_js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.min.js');
	wp_enqueue_script('bootstrap_js');
};
add_action('wp_print_scripts', 'ghhs_found_pets_scripts');
