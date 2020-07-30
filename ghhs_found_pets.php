<?php
/**
 * @link              https://www.21adsmedia.com
 * @since             1.0.0
 * @package           [ghhs_found_pets]
 *
 * @wordpress-plugin
 * Plugin Name:       GHHS Found Pets Shortcode
 * Plugin URI:        https://www.21adsmedia.com
 * Description:       This plugin creates a shortcode that displays all stray cats, dogs and other animals that are currently listed in Greater Huntsville Humane Society's database in Shelterluv.
 * Version:           1.1.0
 * Author:            Andrew Skinner
 * Author URI:        https://www.21adsmedia.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ghhs_found_pets
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

define('PLUGIN_DEBUG', FALSE);
//define('ANIMAL_LINK', 'https://www.shelterluv.com/matchme/adopt/ghhs-a-');
define('REMOVE_TRANSIENT', true);

include_once 'include_styles.php';
include 'display_content.php';

class GHHS_Found_Pets {

	var $request_uri;
	var $args = array(
		'headers' => array(
			'x-api-key' => '7a8f9f04-3052-455f-bf65-54e833f2a5e7',
		),
	);

	public function __construct() {
	}

	public function set_request_uri($request_uri = string) {
		$this->request_uri = $request_uri;
	}

	public function ghhs_found_pets_query_number_animals($request_uri = string, $args = array()) {
		//$request_uri = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable';

		$request = wp_remote_get($request_uri, $args);
		if (is_wp_error($request) || '200' != wp_remote_retrieve_response_code($request)) {
			return 0;
		}
		$temp_data = json_decode(wp_remote_retrieve_body($request));
		if (empty($temp_data)) {

			return 0;
		}
		// total animals published in ShelterLuv
		$animal_count = $temp_data->total_count;

		// get the number of requests we will need to make
		$total_requests = (($animal_count / 100) % 10) + 1;
		if (PLUGIN_DEBUG) {
			echo "<p>Total Request   " . ($total_requests) . "</p>";
		}

		return $total_requests;
	}

}

function ghhs_found_pets_make_request() {

	//if (REMOVE_TRANSIENT) { ghhs_remove_transient(); }
	$found_pets = new GHHS_Found_Pets();
	$found_pets->request_uri = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable';
	$number_requests = $found_pets->ghhs_found_pets_query_number_animals($found_pets->request_uri, $found_pets->args);

	for ($i = 0; $i < $number_requests; $i++) {

		$request_uri[$i] = $found_pets->set_request_uri('https://www.shelterluv.com/api/v1/animals/?status_type=publishable&offset=' . $i . '00&limit=100');
	}

	$args = array(
		'headers' => array(
			'x-api-key' => '7a8f9f04-3052-455f-bf65-54e833f2a5e7',
		),
	);

	$transient = get_transient('ghhs_pets');

	// Yep!  Just return it and we're done.
	if (!empty($transient)) {

		// The function will return here every time after the first time it is run, until the 	transient expires.
		return $transient;

	}
	// Nope!  We gotta make a call.
	else {
		$all_pets = array();
		for ($i = 0; $i < $number_requests; $i++) {

			$request[$i] = wp_remote_get($request_uri[$i], $args);
			if (is_wp_error($request[$i]) || '200' != wp_remote_retrieve_response_code($request[$i])) {
				if (PLUGIN_DEBUG) {
					echo "<p>Bad wp_remote_get Request </p>";
				}

				return;
			}
			$pets[$i] = json_decode(wp_remote_retrieve_body($request[$i]));

			if (empty($pets)) {
				if (PLUGIN_DEBUG) {
					echo "<p>make_request(): No pets to json_decode </p>";
				}

				return;
			}
			//array_push($all_pets, $pets[$i]->animals);
			$all_pets[] = $pets[$i]->animals;
			if (PLUGIN_DEBUG) {
				echo '<pre>';
				print_r($all_pets);
				echo '</pre>';}

		}

		// Save the API response so we don't have to call again until tomorrow.
		set_transient('ghhs_pets', $all_pets, HOUR_IN_SECONDS);

		return $all_pets;
	}
}

function ghhs_remove_transient() {
	delete_transient('ghhs_pets');

}

function ghhs_found_pets_shortcode($attributes) {

	ob_start();
	// get optional attributes and assign default values if not present
	extract(shortcode_atts(array(
		'animal_type' => '',
	), $attributes));

	$pets = ghhs_found_pets_make_request();
	if (empty($pets)) {
		echo "<h1>Failed to get a pets</h1>";
		return;
	}

	$cats = array();
	$dogs = array();
	$others = array();

	$status1 = "Available For Adoption";
	$status2 = "Available for Adoption - Awaiting Spay/Neuter";
	$status3 = "Available for Adoption - In Foster";
	$status4 = "Awaiting Spay/Neuter - In Foster";

	for ($i = 0; $i < count($pets); $i++) {

		foreach ($pets[$i] as $pet) {

			//if (PLUGIN_DEBUG) echo '<pre>'; print_r($pet); echo '</pre>';

			$status = $pet->Status;

			if ($pet->Type === "Cat") {

				if (strcmp($status, $status1) === 0) {

					$cats[] = $pet;

					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				} else if (strcmp($status, $status2) === 0) {

					$cats[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				} else if (strcmp($status, $status3) === 0) {

					$cats[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				} else if (strcmp($status, $status4) === 0) {

					$cats[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				}

//            $cats[] = $pet;

			} else if ($pet->Type === "Dog") {

				if (strcmp($status, $status1) == 0) {

					$dogs[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				} else if (strcmp($status, $status2) == 0) {
					$dogs[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				} else if (strcmp($status, $status3) == 0) {
					$dogs[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				} else if (strcmp($status, $status4) == 0) {
					$dogs[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				}

				//$dogs[] = $pet;

			} else {

				if (strcmp($status, $status1) == 0) {

					$others[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				} else if (strcmp($status, $status2) == 0) {
					$others[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				} else if (strcmp($status, $status3) == 0) {
					$others[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				} else if (strcmp($status, $status4) == 0) {
					$others[] = $pet;
					if (PLUGIN_DEBUG) {
						echo "<p></strong>added:   " . $pet->Name . ":   </strong>" . $pet->Status . "</p>";
					}

				}

				//$others[] = $pet;
			}
		} // end of for loop

	} // end $i counter loop

	if (PLUGIN_DEBUG) {
		echo '<h1 class="red_pet">The number of cats is:  ';
		echo count($cats);
		echo '</h1>';

		echo '<h1 class="red_pet">The number of dogs is:  ' . count($dogs) . '</h1>';
		echo '<h1 class="red_pet">The number of others is:  ' . count($others) . '</h1>';
	}

	// probably should loop over cats, then dogs then others... SPLIT THEM APART!!!!!

	if ($animal_type == "Cats") {
		if (empty($cats)) {
			display_no_animals_available($animal_type);
		} else {
			$i = 0;
			$counter = 0;
			$length = count($cats);
			foreach ($cats as $pet) {
				$counter++;
				if ($i == 0) {

					// print the row open and the first pet
					print_section_opening_html();
					display_animal($pet);

				} else if ($i == ($length - 1)) {

					// print the last pet on this row and close this section
					if ($counter == 1) {

						print_section_opening_html();
						display_animal($pet);
						print_section_closing_html();

					} else if ($counter == 2 || $counter == 3) {

						display_animal($pet);
						print_section_closing_html();
					}
					$counter = 0; // reset the counter

				} else if ($counter == 1) {
					// print the row open and the first pet
					print_section_opening_html();
					display_animal($pet);

				} else if ($counter == 3) {

					// print the last pet on this row and close this section
					display_animal($pet);
					print_section_closing_html();

					$counter = 0; // reset the counter

				} else {

					display_animal($pet);

				}
				$i++;
			}
		}

	} else if ($animal_type == "Dogs") {

		if (empty($dogs)) {
			echo "<h2 style=\"text-align:center;\">No dogs are available to adopt at this time.</h2>";
		} else {
			$i = 0;
			$counter = 0;
			$length = count($dogs);
			foreach ($dogs as $pet) {
				$counter++;
				if ($i == 0) {

					// print the row open and the first pet
					print_section_opening_html();
					display_animal($pet);

				} else if ($i == ($length - 1)) {

					// print the last pet on this row and close this section
					if ($counter == 1) {

						print_section_opening_html();
						display_animal($pet);
						print_section_closing_html();

					} else if ($counter == 2 || $counter == 3) {

						display_animal($pet);
						print_section_closing_html();
					}
					$counter = 0; // reset the counter

				} else if ($counter == 1) {
					// print the row open and the first pet
					print_section_opening_html();
					display_animal($pet);

				} else if ($counter == 3) {

					// print the last pet on this row and close this section
					display_animal($pet);
					print_section_closing_html();

					$counter = 0; // reset the counter

				} else {

					display_animal($pet);

				}
				$i++;
			}
		}

	} else if ($animal_type == "Others") {

		if (empty($others)) {
			display_no_animals_available($animal_type);
		} else {
			$i = 0;
			$counter = 0;
			$length = count($others);
			foreach ($others as $pet) {
				$counter++;
				if ($i == 0) {

					// print the row open and the first pet
					print_section_opening_html();
					display_animal($pet);

				} else if ($i == ($length - 1)) {

					// print the last pet on this row and close this section
					if ($counter == 1) {

						print_section_opening_html();
						display_animal($pet);
						print_section_closing_html();

					} else if ($counter == 2 || $counter == 3) {

						display_animal($pet);
						print_section_closing_html();
					}
					$counter = 0; // reset the counter

				} else if ($counter == 1) {
					// print the row open and the first pet
					print_section_opening_html();
					display_animal($pet);

				} else if ($counter == 3) {

					// print the last pet on this row and close this section
					display_animal($pet);
					print_section_closing_html();

					$counter = 0; // reset the counter

				} else {

					display_animal($pet);

				}
				$i++;
			}
		}
	}

	return ob_get_clean();
}

add_shortcode('ghhs_found_pets', 'ghhs_found_pets_shortcode');

add_action('init', 'github_plugin_updater_test_init');
function github_plugin_updater_test_init() {

	include_once 'ghhs_found_pets_updater.php';

	define('WP_GITHUB_FORCE_UPDATE', true);

	if (is_admin()) {
		// note the use of is_admin() to double check that this is happening in the admin

		$config = array(
			'slug' => plugin_basename(__FILE__),
			'proper_folder_name' => 'GHHS-Found-Pets',
			'api_url' => 'https://api.github.com/repos/askinne2/GHHS-Found-Pets/',
			'raw_url' => 'https://raw.github.com/askinne2/GHHS-Found-Pets/master',
			'github_url' => 'https://github.com/askinne2/GHHS-Found-Pets/',
			'zip_url' => 'https://github.com/askinne2/GHHS-Found-Pets//archive/master.zip',
			'sslverify' => true,
			'requires' => '3.0',
			'tested' => '3.3',
			'readme' => 'README.md',
			'access_token' => '384f872dd1b8671680a5995d07151c4eb58aebad',
		);

		new WP_GitHub_Updater($config);

	}

}
