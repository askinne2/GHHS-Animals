<?php
/**
 * @link              https://github.com/askinne2/GHHS-Found-Pets
 * @since             1.0.0
 * @package           [ghhs_found_pets]
 *
 * @wordpress-plugin
 * Plugin Name:       GHHS Found Pets Shortcode
 * Plugin URI:        https://github.com/askinne2/GHHS-Found-Pets
 *
 * Description:       This plugin creates a shortcode that displays all stray cats, dogs and other animals that are currently listed in Greater Huntsville Humane Society's database in Shelterluv.
 *
 *
 * Version:           1.6.1
 * Author:            Andrew Skinner
 * Author URI:        https://www.21adsmedia.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ghhs_found_pets
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/askinne2/GHHS-Found-Pets

 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

define('PLUGIN_DEBUG', false);
define('REMOVE_TRANSIENT', false);

require_once 'ghhs_found_pets_includes.php';
require_once 'ghhs_found_pets_printer.php';
require_once 'ghhs_found_pets_slideshow.php';
require_once 'ghhs_animals.php';

//require_once 'custom-plugin.php';

class GHHS_Found_Pets {

	var $request_uri;
	var $args;
	var $ghhs_acf;

	public function __construct() {

		$this->args = array(
			'headers' => array(
				'x-api-key' => '7a8f9f04-3052-455f-bf65-54e833f2a5e7',
			),
		);
		$this->ghhs_acf = new GHHS_Animals();
		add_shortcode('ghhs_found_pets', array($this, 'run'));

	}

	/**
	 * Hook into the WordPress activate hook
	 */
	public static function activate() {
		// Do something
	}

	/**
	 * Hook into the WordPress deactivate hook
	 */
	public static function deactivate() {
		// Do something
	}
	public function set_request_uri($request_uri = string) {
		$this->request_uri = $request_uri;
	}

	/* Interacts with Shelterluv to determine total
		     * number of requests needed to process all animals
		     * in the GHHS animal database
		     *
		     * @param string $request_uri
		     * @param array $args
	*/
	public function query_number_animals($request_uri = string, $args = array()) {

		$raw_response = wp_remote_get($request_uri, $args);
		if (is_wp_error($raw_response) || '200' != wp_remote_retrieve_response_code($raw_response)) {
			if (PLUGIN_DEBUG) {
				print('fuck me wp_error');
				print_r($raw_response);
			}

			return 0;
		}
		$pets = json_decode(wp_remote_retrieve_body($raw_response));
		if (empty($pets)) {
			if (PLUGIN_DEBUG) {
				print('fuck me no json to decode');
			}

			return 0;
		}
		// total animals published in ShelterLuv
		$animal_count = $pets->total_count;
		if (PLUGIN_DEBUG) {
			echo "<p>Animal Count   " . ($animal_count) . "</p>";
		}
		// get the number of requests we will need to make
		$total_requests = (($animal_count / 100) % 10) + 1;
		if (PLUGIN_DEBUG) {
			echo "<p>Total Request   " . ($total_requests) . "</p>";
		}

		return $total_requests;
	}

	public function ghhs_remove_transient() {
		delete_transient('ghhs_pets');
	}

	public function make_request($number_requests = int) {

		if (PLUGIN_DEBUG) {
			echo "<h2 style='color:red;'>Number Requests:" . $number_requests . "</h2>";
		}

		// Build our array of request URI's
		for ($i = 0; $i < $number_requests; $i++) {
			$request_uri[$i] = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable&offset=' . $i . '00&limit=' . ($i + 1) . '00';
			if (PLUGIN_DEBUG) {echo "fetching" . $request_uri[$i];}
		}

		/* check if a transient already exists
			         *
			         * if no transient, build a transient and store it
			         *
		*/
		$transient = get_transient('ghhs_pets');
		if (!empty($transient)) {
			return $transient;

		} else {
			$all_pets = array();
			for ($i = 0; $i < $number_requests; $i++) {

				$raw_response[$i] = wp_remote_get($request_uri[$i], $this->args);
				if (is_wp_error($raw_response[$i]) || '200' != wp_remote_retrieve_response_code($raw_response[$i])) {
					if (PLUGIN_DEBUG) {
						echo "<p>Bad wp_remote_get Request </p>";
					}

					return;
				}
				$pets[$i] = json_decode(wp_remote_retrieve_body($raw_response[$i]));

				if (empty($pets)) {
					if (PLUGIN_DEBUG) {
						echo "<p>make_request(): No pets to json_decode </p>";
					}

					return;
				}

				$all_pets[] = $pets[$i]->animals;
				if (PLUGIN_DEBUG) {
					echo '<pre>';
					print_r($all_pets);
					echo '</pre>';}

			}

			// Save the API response so we don't have to call again for one hour.
			set_transient('ghhs_pets', $all_pets, HOUR_IN_SECONDS);

			return $all_pets;
		}
	}

	public function request_and_sort($number_requests = int) {

		$pets = $this->make_request($number_requests);
		/*if (empty($pets)) {
			echo "<h2>Uh oh. Our shelter is experiencing technical difficulties.</h2>";
			echo "<h3>Please email <a href=\"mailto:info@ghhs.org\">info@ghhs.org</a> to let them know about the problem you have experienced. We apologize and will fix the issue ASAP.</h3>";
			return;
		}*/

		$cats = array();
		$dogs = array();
		$others = array();

		/* loop through $pets object and sort according to
			         * statuses. We'll only look for pets currently
			         * available for adoption due to GHHS request
		*/

		$status1 = "Available For Adoption";
		$status2 = "Available for Adoption - Awaiting Spay/Neuter";
		$status3 = "Available for Adoption - In Foster";
		$status4 = "Awaiting Spay/Neuter - In Foster";

		for ($i = 0; $i < count($pets); $i++) {

			foreach ($pets[$i] as $pet) {

				$status = $pet->Status;

				if ($pet->Type === "Cat") {
					switch ($status) {
					case $status1:
						$cats[] = $pet;
						break;
					case $status2:
						$cats[] = $pet;
						break;
					case $status3:
						$cats[] = $pet;
						break;
					case $status4:
						$cats[] = $pet;
						break;
					}

				} else if ($pet->Type === "Dog") {

					switch ($status) {
					case $status1:
						$dogs[] = $pet;
						break;
					case $status2:
						$dogs[] = $pet;
						break;
					case $status3:
						$dogs[] = $pet;
						break;
					case $status4:
						$dogs[] = $pet;
						break;
					}

				} else {

					switch ($status) {
					case $status1:
						$others[] = $pet;
						break;
					case $status2:
						$others[] = $pet;
						break;
					case $status3:
						$others[] = $pet;
						break;
					case $status4:
						$others[] = $pet;
						break;
					}

				}
			} // end of foreach loop

		} // end $i counter loop

		if (PLUGIN_DEBUG) {
			echo '<h1 class="red_pet">The number of cats is:  ' . count($cats) . '</h1>';
			echo '<h1 class="red_pet">The number of dogs is:  ' . count($dogs) . '</h1>';
			echo '<h1 class="red_pet">The number of others is:  ' . count($others) . '</h1>';
		}
		$pets_object = array(
			'dogs' => $dogs,
			'cats' => $cats,
			'others' => $others,
		);
		return $pets_object;
	}

	public function display_pets($pets_object = array(), $animal_type = string, $print_mode = string) {
		// probably should loop over cats, then dogs then others... SPLIT THEM APART!!!!!
		// get optional attributes and assign default values if not present

		$cats = $pets_object['cats'];
		$dogs = $pets_object['dogs'];
		$others = $pets_object['others'];

		if ($print_mode == "Adopt") {

			$pet_printer = new ghhs_found_pets_printer();

			if ($animal_type == "Cats") {
				if (empty($cats)) {
					$pet_printer->display_no_animals_available($animal_type);
				} else {

					$pet_slideshow = new ghhs_found_pets_slideshow();

					$pet_slideshow->display($cats);

				}

			} else if ($animal_type == "Dogs") {

				if (empty($dogs)) {
					$pet_printer->display_no_animals_available($animal_type);

				} else {

					$pet_slideshow = new ghhs_found_pets_slideshow();

					$pet_slideshow->display($dogs);

				}

			} else if ($animal_type == "Others") {

				if (empty($others)) {
					$pet_printer->display_no_animals_available($animal_type);
				} else {

					$pet_slideshow = new ghhs_found_pets_slideshow();

					$pet_slideshow->display($others);

				}
			}
		} else if ($print_mode == "Slideshow") {

			$pet_slideshow = new ghhs_found_pets_slideshow();

			$pet_slideshow->display_all_pictures(array_merge($cats, $dogs, $others));

		} else {

			print("<h2>Wrong Use of shortcode. Please try:</h2>");
			print("<h3>[ghhs_found_pets animal_type='Dogs/Cats/Others' mode='Adopt/Slideshow']</h3>");
		}

		return;
	}

	public function run($attributes = string) {

		//$found_pets = new GHHS_Found_Pets();

		if (REMOVE_TRANSIENT) {
			$this->ghhs_remove_transient();
		}

		$this->request_uri = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable';
		$number_requests = $this->query_number_animals($this->request_uri, $this->args);

		ob_start();

		$pets_object = $this->request_and_sort($number_requests);

		extract(shortcode_atts(array(
			'animal_type' => '',
			'mode' => '',
		), $attributes));

		if (PLUGIN_DEBUG) {
			echo "<h2>attributes - ";
			print_r($attributes);
			echo "</h2>";

		}
		$animal_type = $attributes['animal_type'];
		$print_mode = $attributes['mode'];

		$this->display_pets($pets_object, $animal_type, $print_mode);

		return ob_get_clean();

	}

} // end class definition

//$a = new GHHS_Animals();
//$a->register_fields();
//function is_adopt_page() {
//if (is_page('Archive: Animals')) {

if (class_exists('GHHS_Found_Pets')) {
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('GHHS_Found_Pets', 'activate'));
	register_deactivation_hook(__FILE__, array('GHHS_Found_Pets', 'deactivate'));

	// run GHHS_Found_pets shortcode
	$pets = new GHHS_Found_Pets();
/*
add_filter('template_include', 'ghhs_archive_animal_template');

function ghhs_archive_animal_template($template) {
global $post;
if (is_archive() && $post->post_type == 'animal') {
if (file_exists(plugin_dir_path(__FILE__) . 'templates/archive-animal.php')) {

$archive_template = plugin_dir_path(__FILE__) . 'templates/archive-animal.php';
}
return $archive_template;
} else if (is_archive()) {
echo "<h2>fuck</h2>";
return $single;

} else if ($post->post_type == 'animal') {
// Checks for single template by post type

if (file_exists(plugin_dir_path(__FILE__) . 'templates/single-animal.php')) {

$single = plugin_dir_path(__FILE__) . 'templates/single-animal.php';
return $single;
}

}

}
 */
}
