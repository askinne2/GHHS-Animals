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

if (!class_exists("GHHS_Animals_ACF")) {
	/**
	 * class:   GHHS_Animals
	 * desc:    plugin class to allow reports be pulled from multipe GA accounts
	 */
	class GHHS_Animals_ACF {
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
				return false;
			}
		} // END public function __construct()

	} // END class GHHS_Animals
} // END if(!class_exists("GHHS_Animals"))
