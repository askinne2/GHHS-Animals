<?php
/*
Description: Defines an Advanced Custom Field Post Type (GHHS_Animals) using ACF Pro methods
 *
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
// Define path and URL to the ACF plugin.
define('MY_ACF_PATH', plugins_url(plugin_basename(__DIR__)) . '/includes/acf/');
define('MY_ACF_URL', plugins_url(plugin_basename(__DIR__)) . '/includes/acf/');
//require_once '/path/to/wp-load.php';

if (!class_exists("GHHS_Animals")) {
	/**
	 * class:   GHHS_Animals
	 * desc:    plugin class to allow reports be pulled from multipe GA accounts
	 */
	class GHHS_Animals {
		/**
		 * Created an instance of the GHHS_Animals class
		 */
		public function __construct() {
			// Set up ACF
			add_filter('acf/settings/path', function () {
				return sprintf("%s/includes/acf-pro/", dirname(__FILE__));
			});
			add_filter('acf/settings/dir', function () {
				return sprintf("%s/includes/acf-pro/", plugin_dir_url(__FILE__));
			});
			require_once sprintf("%s/includes/acf-pro/acf.php", dirname(__FILE__));

			// Settings managed via ACF
			require_once sprintf("%s/includes/settings.php", dirname(__FILE__));
			$settings = new GHHS_Animals_Settings(plugin_basename(__FILE__));

			// CPT for example post type
			require_once sprintf("%s/includes/animal-post-type.php", dirname(__FILE__));
			$exampleposttype = new GHHS_Animals_PostType();

			// (Optional) Hide the ACF admin menu item.
			add_filter('acf/settings/show_admin', 'my_acf_settings_show_admin');
			function my_acf_settings_show_admin($show_admin) {
				return true;
			}
			//add_action('init', array(&$this, 'new_animal_post'));

		} // END public function __construct()

		public function new_animal_post() {
			//if (get_post_type($post_id) == 'animal') {

			$post_id = wp_insert_post(array(
				'post_title' => 'Jesus',
				'post_type' => 'animal',
				'post_content' => 'demo text',
			));
			if ($post_id) {
				printf('<h2>fuck</h2>');
			} else {
				printf('<h2>fuck</h2>');
			}

		} // END public function new_animal_post()

	} // END class GHHS_Animals
} // END if(!class_exists("GHHS_Animals"))
