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

define('PLUGIN_DEBUG', true);
define('REMOVE_TRANSIENT', false);

define('GHHS_UPLOADS', 'wp-content/uploads/ghhs-animals');

require_once 'ghhs_found_pets_includes.php';
require_once 'ghhs_found_pets_printer.php';
require_once 'ghhs_found_pets_slideshow.php';
require_once 'ghhs_animals.php';

//require_once 'custom-plugin.php';

class GHHS_Found_Pets {

	var $request_uri;
	var $args;
	var $ghhs_acf;
	var $status_array = array(
		'status1' => 'Available For Adoption',
		'status2' => 'Available for Adoption - Awaiting Spay/Neuter',
		'status3' => 'Available for Adoption - In Foster',
		'status4' => 'Awaiting Spay/Neuter - In Foster',
	);

	const CRON_HOOK = 'ghhs_update_animals';

	public function __construct() {

		$this->args = array(
			'headers' => array(
				'x-api-key' => '7a8f9f04-3052-455f-bf65-54e833f2a5e7',
			),
		);

		$this->ghhs_acf = new GHHS_Animals();
		//	add_filter('init', array($this, 'do_animal_post'));
		//add_filter('acf/update_value/name=cover_photo', array(&$this, 'acf_set_featured_image', 10, 3));

		add_action('trashed_post', array($this, 'animal_delete'));
		//add_action('init', array($this, 'run'));
		//add_action(self::CRON_HOOK, array($this, 'run'));
		add_shortcode('ghhs_found_pets', array(&$this, 'run'));

	}

	/**
	 * Hook into the WordPress activate hook
	 */
	public static function activate() {
		/* Do something
			//Use wp_next_scheduled to check if the event is already scheduled
			$timestamp = wp_next_scheduled(self::CRON_HOOK);

			//If $timestamp === false schedule daily backups since it hasn't been done previously
			if ($timestamp === false) {
				//Schedule the event for right now, then to repeat daily using the hook 'update_whatToMine_api'
				wp_schedule_event(time(), 'hourly', self::CRON_HOOK);
			}
		*/

		$upload = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = $upload_dir . '/ghhs-animals';
		if (!is_dir($upload_dir)) {
			mkdir($upload_dir, 0755);
		}
	}

	/**
	 * Hook into the WordPress deactivate hook
	 */
	public static function deactivate() {
		// Do something
		// Get the timestamp for the next event.
		$timestamp = wp_next_scheduled(self::CRON_HOOK);
		wp_unschedule_event($timestamp, self::CRON_HOOK);
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
				print('wp_error:');
				print_r($raw_response);
			}

			return 0;
		}
		$pets = json_decode(wp_remote_retrieve_body($raw_response));
		if (empty($pets)) {
			if (PLUGIN_DEBUG) {
				print('No JSON to decode');
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
				if (!PLUGIN_DEBUG) {
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

	public function create_and_update_animals($pets_object) {

		$dogs = $pets_object['dogs'];
		$cats = $pets_object['cats'];

		$postid = $this->do_animal_post($dogs[0]);
		$postid = $this->do_animal_post($cats[0]);
		/*
			foreach ($dogs as $dog) {

				$postid = $this->do_animal_post($dog);
				if ($postid) {
					//printf('<h2>successsful do_animal_post: %s</h2>', $postid);
				} else {
					printf('<h2>NOOOOOO insert</h2>');
				}
			} //end foreach dogs loop
		*/
		//$this->animal_delete($postid);
		//$this->delete_animals();

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

	public function animal_delete($post_id) {
		if (get_post_type($post_id) == 'animal') {
			// <-- members type posts
			// Force delete

			printf('<h2>post id:</h2>');
			print_r($post_id);
			$post_attachments = get_attached_media('', $post_id);

			printf('<h3>post attachments:</h3>');
			print_r($post_attachments);

			if ($post_attachments) {

				foreach ($post_attachments as $attachment) {

					wp_delete_attachment($attachment->ID, true);

				}

			}
			wp_delete_post($post_id, true);
		}
	}

	public function delete_all_animals() {

		$delete_post = array(
			'post_type' => 'animal',
			'post_status' => 'publish',
		);
		$posts = new WP_Query($delete_post);
		if ($posts->have_posts()) {

			foreach ($posts->get_posts() as $post) {
				//var_dump($post);
				$this->animal_delete($post->ID);
			}

		}

	}

	function upload_image($url, $post_id) {
		// Add Featured Image to Post
		$image_url = $url; // Define the image URL here
		$image_name = 'animal-' . $post_id . '.png';
		$upload_dir = wp_upload_dir(); // Set upload folder

		// Set attachment data
		$attachment = array(
			'name' => $image_name,
			'posts_per_page' => 1,
			'post_type' => 'attachment',
		);

		// check if image exists
		$attachment_check = new Wp_Query($attachment);

		if ($attachment_check->have_posts()) {
			printf('<h2>attachment exists</h2>');

		} else {

			$image_data = file_get_contents($image_url); // Get image data
			$unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
			$filename = basename($unique_file_name); // Create image file name

			// Check folder permission and define file location
			if (wp_mkdir_p($upload_dir['path'])) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}

			// Create the image  file on the server
			file_put_contents($file, $image_data);

			// Check image file type
			$wp_filetype = wp_check_filetype($filename, null);

			// Set attachment data
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => sanitize_file_name($filename),
				'post_content' => '',
				'post_status' => 'inherit',
			);

			// Create the attachment
			$attach_id = wp_insert_attachment($attachment, $file, $post_id);

			// Include image.php
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Define attachment metadata
			$attach_data = wp_generate_attachment_metadata($attach_id, $file);

			// Assign metadata to attachment
			wp_update_attachment_metadata($attach_id, $attach_data);

			// And finally assign featured image to post
			set_post_thumbnail($post_id, $attach_id);
			return $attach_id;
		}
	}

	public function do_animal_post($animal) {
		//if (get_post_type($post_id) == 'animal') {

		$new_animal = array(
			'post_title' => $animal->Name,
			'post_type' => 'animal',
			'post_content' => $animal->Description,
			'post_status' => 'publish',
			//'_thumbnail_id' => $animal->CoverPhoto,
			'comment_status' => 'closed', // if you prefer
			'ping_status' => 'closed', // if you prefer
		);

		$post_id = get_page_by_title($new_animal['post_title'], OBJECT, 'animal');

		if (!$post_id) {

			if (PLUGIN_DEBUG) {
				printf('<h2>NEW ANIMAL</h2>');
				print_r($animal);
				printf('<h5>Name %s</h5>', $animal->Name);
			}

			// CREATE A NEW ANIMAL POST AND UPDATE THE META FIELDS
			$new_post_id = wp_insert_post($new_animal);
			$blah = wp_set_object_terms(
				$new_post_id,
				array('0' => 'All Animal', '1' => $animal->Type),
				'adopt-animals'
			);

			$post_thumbnail = $this->upload_image($animal->CoverPhoto, $new_post_id);

			if ($new_post_id) {
				// insert post meta
				//add_post_meta($new_post_id, '_thumbnail_id', $post_thumbnail);
				add_post_meta($new_post_id, 'animal_id', $animal->ID);
				add_post_meta($new_post_id, 'animal_name', $animal->Name);
				add_post_meta($new_post_id, 'cover_photo', $animal->CoverPhoto);
				add_post_meta($new_post_id, 'color', $animal->Color);
				add_post_meta($new_post_id, 'breed', $animal->Breed);
				add_post_meta($new_post_id, 'animal_type', $animal->Type);
				add_post_meta($new_post_id, 'status', $animal->Status);
				add_post_meta($new_post_id, 'sex', $animal->Sex);
				add_post_meta($new_post_id, 'age', number_format($animal->Age / 12, 1, ' years, ', '') . ' months');
				add_post_meta($new_post_id, 'bio', $animal->Description);
				add_post_meta($new_post_id, 'animal_size', $animal->Size);
				add_post_meta($new_post_id, 'last_update_time', $animal->LastUpdatedUnixTime);
				if (isset($animal->AdoptionFeeGroup->Price)) {
					add_post_meta($new_post_id, 'adoption_fee', $animal->AdoptionFeeGroup->Price);
				}

				$adopt_link = 'https://www.shelterluv.com/matchme/adopt/ghhs-a-' . $animal->ID;
				add_post_meta($new_post_id, 'adopt_link', $adopt_link);

				printf('<h2 class="red_pet">Photos for %s</h2>', $animal->Name);
				print_r($animal->Photos);
				foreach ($animal->Photos as $photo) {
					printf('<h4>Add Photo: %s</h4>', $photo);
					add_post_meta($new_post_id, 'photos', $photo);
				}
			} else {

				printf('<h2>insert post failed for %s</h2>', $animal->Name);
			}

			$post_id = $new_post_id;

			// ANIMAL ALREADY EXISTS!
			// EITHER UPDATE OR DELETE ACCORDING TO STATUS
		} else {

			$animal_status = get_post_meta($post_id->ID, 'status', true);

			if (!in_array($animal_status, $this->status_array)) {
				printf('<h5 class="red_pet">Please delete animal: %s</h5>', $animal->Name);
				$this->animal_delete($post_id->ID);
			} else {
				printf('<h5>Status Match: %s</h5>', $animal->Name);
			}

			$postUpdateTime = get_post_meta($post_id->ID, 'last_update_time', true);

			if (!PLUGIN_DEBUG) {
				printf('<h2>Time</h2>');
				print_r($postUpdateTime);
			}

			// ONLY UPDATE IF THE TWO TIMESTAMPS DO NOT MATCH
			if ($animal->LastUpdatedUnixTime != $postUpdateTime) {
				$update_animal = array(
					'post_id' => $post_id->ID,
					'post_title' => $animal->Name,
					'post_type' => 'animal',
					'post_content' => $animal->Description,
					'post_status' => 'publish',
					'comment_status' => 'closed', // if you prefer
					'ping_status' => 'closed', // if you prefer
				);

				if (PLUGIN_DEBUG) {
					printf('<h2>UPDATE ANIMAL</h2>');
					print_r($animal);
					printf('<h5>Name %s</h5>', $animal->Name);
				}
				//$update_post_id = wp_update_post($update_animal, true)
				$post_thumbnail = $this->upload_image($animal->CoverPhoto, $post_id->ID);
				//update_post_meta($post_id->ID, '_thumbnail_id', $post_thumbnail);

				update_post_meta($post_id->ID, 'animal_id', $animal->ID);
				update_post_meta($post_id->ID, 'animal_name', $animal->Name);
				update_post_meta($post_id->ID, 'cover_photo', $animal->CoverPhoto);
				update_post_meta($post_id->ID, 'color', $animal->Color);
				update_post_meta($post_id->ID, 'breed', $animal->Breed);
				update_post_meta($post_id->ID, 'animal_type', $animal->Type);
				update_post_meta($post_id->ID, 'status', $animal->Status);
				update_post_meta($post_id->ID, 'sex', $animal->Sex);
				update_post_meta($post_id->ID, 'age', number_format($animal->Age / 12, 1, ' years, ', '') . ' months');
				update_post_meta($post_id->ID, 'bio', $animal->Description);
				update_post_meta($post_id->ID, 'animal_size', $animal->Size);
				update_post_meta($post_id->ID, 'last_update_time', $animal->LastUpdatedUnixTime);

				$adopt_link = 'https://www.shelterluv.com/matchme/adopt/ghhs-a-' . $animal->ID;
				update_post_meta($post_id->ID, 'adopt_link', $adopt_link);

				/***** THIS BREAKS ELEMENTOR ****/
				//$this->acf_set_featured_image($picture, $post_id->ID);
				/***** THIS BREAKS ELEMENTOR ****/

				//add_post_meta($post_id, 'cover_photo', 'http://ghhs/wp-content/uploads/2020/04/Cameo-1-1-scaled.jpg');
			} // end timestamp comparison
			else {
				$adopt_link = 'https://www.shelterluv.com/matchme/adopt/ghhs-a-' . $animal->ID;
				update_post_meta($post_id->ID, 'adopt_link', $adopt_link);

				$post_thumbnail = $this->upload_image($animal->CoverPhoto, $post_id->ID);
				var_dump($post_thumbnail);
				//update_post_meta($post_id->ID, '_thumbnail_id', $post_thumbnail);
			}
		}
		return $post_id;
	} // END public function new_animal_post()

	public function run($attributes = string) {

		//$found_pets = new GHHS_Found_Pets();

		if (REMOVE_TRANSIENT) {
			$this->ghhs_remove_transient();
		}

		$this->request_uri = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable';
		$number_requests = $this->query_number_animals($this->request_uri, $this->args);

		ob_start();

		$pets_object = $this->request_and_sort($number_requests);

/*
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
 */

		//$this->display_pets($pets_object, $animal_type, $print_mode);
		$this->create_and_update_animals($pets_object);

		return ob_get_clean();

	}

} // end class definition

function ghhs_archive_animal_template($template) {

	global $post;

	if (is_archive() && $post->post_type == 'animal') {
		if (file_exists(plugin_dir_path(__FILE__) . 'templates/archive-animal.php')) {

			$archive_template = plugin_dir_path(__FILE__) . 'templates/archive-animal.php';
		}
		return $archive_template;

	} else if (is_single() && $post->post_type == 'animal') {
// Checks for single template by post type

		if (file_exists(plugin_dir_path(__FILE__) . 'templates/single-animal.php')) {

			$template = plugin_dir_path(__FILE__) . 'templates/single-animal.php';
			return $template;
		}

	} else {
		return $template;
	}

}
add_filter('template_include', 'ghhs_archive_animal_template', 12);

if (class_exists('GHHS_Found_Pets')) {
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('GHHS_Found_Pets', 'activate'));
	register_deactivation_hook(__FILE__, array('GHHS_Found_Pets', 'deactivate'));

	// run GHHS_Found_pets shortcode

	function custom_http_request_timeout() {
		return 15;
	}
	add_filter('http_request_timeout', 'custom_http_request_timeout');
	$pets = new GHHS_Found_Pets();

}
