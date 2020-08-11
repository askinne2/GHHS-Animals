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
 * Version:           1.3.6
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

define('PLUGIN_DEBUG', true);
define('REMOVE_TRANSIENT', false);

require_once 'ghhs_found_pets_includes.php';
require_once 'ghhs_found_pets_printer.php';

class GHHS_Found_Pets {

	var $request_uri;
	var $args;

	public function __construct() {
		$this->args = array(
			'headers' => array(
				'x-api-key' => $_ENV["GHHS_TOKEN"],
			),
		);
		add_shortcode('ghhs_found_pets', array($this, 'run'));

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
			return 0;
		}
		$pets = json_decode(wp_remote_retrieve_body($raw_response));
		if (empty($pets)) {

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
		if (empty($pets)) {
			echo "<h2>Uh oh. Our shelter is experiencing technical difficuluties.</h2>";
			echo "<h3>Please email <a href=\"mailto:info@ghhs.org\">info@ghhs.org</a> to let them know about the problem you have experienced. We apologize and will fix the issue ASAP.</h3>";
			return;
		}

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

	public function display_pets($pets_object = array(), $attributes = string) {
		// probably should loop over cats, then dogs then others... SPLIT THEM APART!!!!!
		// get optional attributes and assign default values if not present

		if (PLUGIN_DEBUG) {
			echo "<h2>attributes - ";
			print_r($attributes);
			echo "</h2>";
		}

		extract(shortcode_atts(array(
			'animal_type' => '',
		), $attributes));

		$cats = $pets_object['cats'];
		$dogs = $pets_object['dogs'];
		$others = $pets_object['others'];

		$pet_printer = new ghhs_found_pets_printer();

		if ($animal_type == "Cats") {
			if (empty($cats)) {
				$pet_printer->display_no_animals_available($animal_type);
			} else {
				$i = 0;
				$counter = 0;
				$length = count($cats);
				foreach ($cats as $pet) {
					$counter++;
					if ($i == 0) {

						// print the row open and the first pet
						$pet_printer->print_section_opening_html();
						$pet_printer->display_animal($pet);

					} else if ($i == ($length - 1)) {

						// print the last pet on this row and close this section
						if ($counter == 1) {

							$pet_printer->print_section_opening_html();
							$pet_printer->display_animal($pet);
							$pet_printer->print_section_closing_html();

						} else if ($counter == 2 || $counter == 3) {

							$pet_printer->display_animal($pet);
							$pet_printer->print_section_closing_html();
						}
						$counter = 0; // reset the counter

					} else if ($counter == 1) {
						// print the row open and the first pet
						$pet_printer->print_section_opening_html();
						$pet_printer->display_animal($pet);

					} else if ($counter == 3) {

						// print the last pet on this row and close this section
						$pet_printer->display_animal($pet);
						$pet_printer->print_section_closing_html();

						$counter = 0; // reset the counter

					} else {

						$pet_printer->display_animal($pet);

					}
					$i++;
				}
			}

		} else if ($animal_type == "Dogs") {

			if (empty($dogs)) {
				$pet_printer->display_no_animals_available($animal_type);

			} else {
				$i = 0;
				$counter = 0;
				$length = count($dogs);
				foreach ($dogs as $pet) {
					$counter++;
					if ($i == 0) {

						// print the row open and the first pet
						$pet_printer->print_section_opening_html();
						$pet_printer->display_animal($pet);

					} else if ($i == ($length - 1)) {

						// print the last pet on this row and close this section
						if ($counter == 1) {

							$pet_printer->print_section_opening_html();
							$pet_printer->display_animal($pet);
							$pet_printer->print_section_closing_html();

						} else if ($counter == 2 || $counter == 3) {

							$pet_printer->display_animal($pet);
							$pet_printer->print_section_closing_html();
						}
						$counter = 0; // reset the counter

					} else if ($counter == 1) {
						// print the row open and the first pet
						$pet_printer->print_section_opening_html();
						$pet_printer->display_animal($pet);

					} else if ($counter == 3) {

						// print the last pet on this row and close this section
						$pet_printer->display_animal($pet);
						$pet_printer->print_section_closing_html();

						$counter = 0; // reset the counter

					} else {

						$pet_printer->display_animal($pet);

					}
					$i++;
				}
			}

		} else if ($animal_type == "Others") {

			if (empty($others)) {
				$pet_printer->display_no_animals_available($animal_type);
			} else {
				$i = 0;
				$counter = 0;
				$length = count($others);
				foreach ($others as $pet) {
					$counter++;
					if ($i == 0) {

						// print the row open and the first pet
						$pet_printer->print_section_opening_html();
						$pet_printer->display_animal($pet);

					} else if ($i == ($length - 1)) {

						// print the last pet on this row and close this section
						if ($counter == 1) {

							$pet_printer->print_section_opening_html();
							$pet_printer->display_animal($pet);
							$pet_printer->print_section_closing_html();

						} else if ($counter == 2 || $counter == 3) {

							$pet_printer->display_animal($pet);
							$pet_printer->print_section_closing_html();
						}
						$counter = 0; // reset the counter

					} else if ($counter == 1) {
						// print the row open and the first pet
						$pet_printer->print_section_opening_html();
						$pet_printer->display_animal($pet);

					} else if ($counter == 3) {

						// print the last pet on this row and close this section
						$pet_printer->display_animal($pet);
						$pet_printer->print_section_closing_html();

						$counter = 0; // reset the counter

					} else {

						$pet_printer->display_animal($pet);

					}
					$i++;
				}
			}
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

		$this->display_pets($pets_object, $attributes);
		return ob_get_clean();

	}

} // end class definition

// run GHHS_Found_pets shortcode
new GHHS_Found_Pets();
