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
 * Version:           2.0.0
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
define('LOCAL_JSON', false);
define('GHHS_UPLOADS', 'wp-content/uploads/ghhs-animals');

require_once 'ghhs_found_pets_includes.php';
require_once 'ghhs_found_pets_printer.php';
require_once 'ghhs_found_pets_slideshow.php';
require_once 'ghhs_animals.php';

class GHHS_Found_Pets {

	const CRON_HOOK = 'ghhs_update_animals';

	var $request_uri;
	var $args;
	var $ghhs_animals;
	var $multiple_request_flag = 0;
	var $petID_array;
	var $ghhs_pets_object;

	var $status_array = array(
		'status1' => 'Available For Adoption',
		'status2' => 'Available for Adoption - Awaiting Spay/Neuter',
		'status3' => 'Available for Adoption - In Foster',
		'status4' => 'Awaiting Spay/Neuter - In Foster',
	);

	public function __construct() {

		$this->args = array(
			'headers' => array(
				'x-api-key' => '7a8f9f04-3052-455f-bf65-54e833f2a5e7',
			),
		);
		$this->ghhs_animals = new GHHS_Animals();
		add_action('trashed_post', array($this, 'delete_animal_post'));
		add_filter('pre_get_posts', array($this, 'animals_change_posts_per_page'));
		add_filter('template_include', array($this, 'ghhs_single_animal_template'), 9999);
		add_filter('template_include', array($this, 'ghhs_archive_animal_template'), 9999);

		//add_action(self::CRON_HOOK, array($this, 'run'));
		add_shortcode('ghhs_found_pets', array($this, 'run'));
		add_shortcode('ghhs_slideshow', array($this, 'display_pets'));
	}

	/**
	 * Hook into the WordPress activate hook
	 */
	public static function activate() {
		//add_action('init', new GHHS_Animals());

		// Do something
		//Use wp_next_scheduled to check if the event is already scheduled
		$timestamp = wp_next_scheduled(self::CRON_HOOK);

		//If $timestamp === false schedule daily backups since it hasn't been done previously
		if ($timestamp === false) {
			//Schedule the event for right now, then to repeat daily using the hook
			wp_schedule_event(time(), 'hourly', self::CRON_HOOK);
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
		self::delete_all_animals();
	}

	public function set_request_uri($request_uri = string) {
		$this->request_uri = $request_uri;
	}

	public function super_request($args = array()) {
		//$this->ghhs_remove_transient();
		$transient = get_transient('super_ghhs_pets');
		if (!empty($transient)) {
			if (PLUGIN_DEBUG) {
				printf('<h2 class="red_pet">TRANSIENT FOUND</h2>');
			}
			return $transient;

		} else {
			$number_requests = 77;
			// Build our array of request URI's and make more calls
			for ($i = 0; $i < $number_requests; $i++) {
				$request_uri[$i] = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable&offset=' . $i . '00&limit=100';
				if (PLUGIN_DEBUG) {echo "fetching" . $i;}
			}

			$all_pets = array();
			for ($i = 0; $i < $number_requests; $i++) {

				$raw_response[$i] = wp_remote_get($request_uri[$i], $this->args);
				if (is_wp_error($raw_response[$i]) || '200' != wp_remote_retrieve_response_code($raw_response[$i])) {
					if (PLUGIN_DEBUG) {
						echo "<p>Bad wp_remote_get Request </p>";
					}

					return;
				}
				$all_pets[$i] = json_decode(wp_remote_retrieve_body($raw_response[$i]));
			}
		}
		set_transient('super_ghhs_pets', $all_pets, HOUR_IN_SECONDS);

		return $all_pets;
	}
	/*
		* returns an unsorted $pets object of all published animals from shelterluv
		*
		*/
	public function make_request($request_uri = string, $args = array()) {

		$transient = get_transient('ghhs_pets');
		if (!empty($transient)) {
			if (PLUGIN_DEBUG) {
				printf('<h2 class="red_pet">TRANSIENT FOUND</h2>');
			}
			return $transient->animals;

		} else {
			if (LOCAL_JSON) {
				$jsonpets = file_get_contents(plugins_url(plugin_basename(__DIR__)) . '/acf-json/animals.json');
				$pets = json_decode($jsonpets);
				//print_r($pets);
				return $pets->animals;

			} else {
				$request_uri = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable';
				$raw_response = wp_remote_get($request_uri, $args);
				if (is_wp_error($raw_response) || '200' != wp_remote_retrieve_response_code($raw_response)) {
					if (PLUGIN_DEBUG) {
						print('wp_error:');
						print_r($raw_response);
					}

					return 0;
				}
				$pets = json_decode(wp_remote_retrieve_body($raw_response));

			}
			if (empty($pets)) {
				if (PLUGIN_DEBUG) {
					print('No JSON to decode');
				}

				return;
			}
			// total animals published in ShelterLuv
			$animal_count = $pets->total_count;
			if (PLUGIN_DEBUG) {
				echo "<p>Animal Count   " . ($animal_count) . "</p>";
			}
			$total_requests = (($animal_count / 100) % 10) + 1;
			if (PLUGIN_DEBUG) {
				echo "<p>Total Request   " . ($total_requests) . "</p>";
			}

			if ($animal_count < 100) {

				// low animal count, we can just set transient and return animals 0 -> 100.
				if (PLUGIN_DEBUG) {
					printf('<h2 class="red_pet">SET TRANSIENT LOW ANIMALS</h2>');
				}
				set_transient('ghhs_pets', $pets, HOUR_IN_SECONDS);
				return $pets->animals;

			} else {

				$this->multiple_request_flag = true;
				if (LOCAL_JSON) {

					$jsonpets = file_get_contents(plugins_url(plugin_basename(__DIR__)) . '/acf-json/animals.json');
					$pets = json_decode($jsonpets);
					return $pets->animals;

				} else {
					// Build our array of request URI's and make more calls
					for ($i = 0; $i < $number_requests; $i++) {
						$request_uri[$i] = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable&offset=' . $i . '00&limit=100';
						if (PLUGIN_DEBUG) {echo "fetching" . $request_uri[$i];}
					}

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
					}

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
						echo '</pre>';
					}

				}

				// Save the API response so we don't have to call again for one hour.
				if (PLUGIN_DEBUG) {
					printf('<h2 class="red_pet">SET TRANSIENT HIGH ANIMALS</h2>');
				}
				set_transient('ghhs_pets', $pets, HOUR_IN_SECONDS);

				return $all_pets;
			}

		}

	}

	public function ghhs_remove_transient() {
		delete_transient('ghhs_pets');
		delete_transient('super_ghhs_pets');
	}

	public function request_and_sort($request_uri = string, $args = array()) {

		$pets = $this->make_request($request_uri, $args);
		if (empty($pets)) {
			echo "<h5>Uh oh. Our shelter is experiencing technical difficulties.</h5>";
			echo "<p>Please email <a href=\"mailto:info@ghhs.org\">info@ghhs.org</a> to let them know about the problem you have experienced. We apologize and will fix the issue ASAP.</p>";
			return;
		}

		$cats = array();
		$dogs = array();
		$others = array();

		/* loop through $pets object and sort according to
				         * statuses. We'll only look for pets currently
				         * available for adoption due to GHHS request

			$status1 = "Available For Adoption";
			$status2 = "Available for Adoption - Awaiting Spay/Neuter";
			$status3 = "Available for Adoption - In Foster";
			$status4 = "Awaiting Spay/Neuter - In Foster";
		*/

		if ($this->multiple_request_flag) {
			for ($i = 0; $i < count($pets); $i++) {

				foreach ($pets as $pet) {

					if ($pet->Type === "Cat") {
						if (in_array($pet->Status, $this->status_array)) {
							$cats[] = $pet;

							// set $petID_array array for use later in deleting adopted pet
							$this->petID_array[] = $pet->ID;
						}

					} else if ($pet->Type === "Dog") {

						if (in_array($pet->Status, $this->status_array)) {
							$dogs[] = $pet;

							// set $petID_array array for use later in deleting adopted pet
							$this->petID_array[] = $pet->ID;
						}

					} else {

						if (in_array($pet->Status, $this->status_array)) {
							$others[] = $pet;

							// set $petID_array array for use later in deleting adopted pet
							$this->petID_array[] = $pet->ID;
						}

					}
				} // end of foreach loop

			} // end $i counter loop
		} else {
			foreach ($pets as $pet) {

				if ($pet->Type === "Cat") {
					if (in_array($pet->Status, $this->status_array)) {
						$cats[] = $pet;
						// set $petID_array array for use later in deleting adopted pet
						$this->petID_array[] = $pet->ID;

					}

				} else if ($pet->Type === "Dog") {

					if (in_array($pet->Status, $this->status_array)) {
						$dogs[] = $pet;
						// set $petID_array array for use later in deleting adopted pet
						$this->petID_array[] = $pet->ID;

					}

				} else {

					if (in_array($pet->Status, $this->status_array)) {
						$others[] = $pet;
						// set $petID_array array for use later in deleting adopted pet
						$this->petID_array[] = $pet->ID;

					}

				}
			} // end of foreach loop
		}

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
		$others = $pets_object['others'];

		foreach ($dogs as $dog) {

			$postid = $this->create_animal_post($dog);
			if ($postid) {
				//printf('<h2>successsful create_animal_post: %s</h2>', $postid);
			} else {
				printf('<h2>NOOOOOO insert</h2>');
			}
		} //end foreach dogs loop
		foreach ($cats as $cat) {

			$postid = $this->create_animal_post($cat);
			if ($postid) {
				//printf('<h2>successsful create_animal_post: %s</h2>', $postid);
			} else {
				printf('<h2>NOOOOOO insert</h2>');
			}
		} //end foreach dogs loop
		foreach ($others as $other) {

			$postid = $this->create_animal_post($other);
			if ($postid) {
				//printf('<h2>successsful create_animal_post: %s</h2>', $postid);
			} else {
				printf('<h2>NOOOOOO insert</h2>');
			}
		} //end foreach dogs loop

	}

	public function delete_adopted_animals($petID_array = array()) {

		/* search through created animal posts
			 * get array of animal post IDs
			 * if animal_post_id IS NOT FOUND in request $petID_array -> delete_animal_post()
		*/
		$animal_post = array(
			'post_type' => 'animal',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		);
		$query = new WP_Query($animal_post);
		$posts = $query->posts;

		$animal_ids = (object) [
			'ghhs_id' => array(),
			'wp_id' => array(),
		];
		if ($posts) {
			$i = 0;
			foreach ($posts as $post) {
				$animal_ids->ghhs_id[$i] = get_field('animal_id', $post->ID);
				$animal_ids->wp_id[$i] = $post->ID;
				$i++;
			}

		}

		$i = 0;
		foreach ($animal_ids->ghhs_id as $id) {

			if (!in_array($id, $this->petID_array)) {
				$post_to_delete = $animal_ids->wp_id[$i];
				printf('<h2> delete this animal: %d ORRRRR %d</h2>', $id, $animal_ids->wp_id[$i]);
				$this->delete_animal_post($post_to_delete);
			}
			$i++;

		}

	}

	public function display_pets($pets_object = array(), $animal_type = string, $print_mode = string) {
		// probably should loop over cats, then dogs then others... SPLIT THEM APART!!!!!
		// get optional attributes and assign default values if not present

		$transient = get_transient('ghhs_pets');
		if (!empty($transient)) {
			if (PLUGIN_DEBUG) {
				printf('<h2 class="red_pet">TRANSIENT FOUND</h2>');
			}
			$this->ghhs_pets_object = $transient->animals;
		} else {
			printf('<h2> no transient can\'t run. </h2>');
			return;
		}

		$pet_slideshow = new ghhs_found_pets_slideshow();
		ob_start();
		$pet_slideshow->display_all_pictures($this->ghhs_pets_object);

		return ob_get_clean();
	}

	public static function delete_animal_post($post_id) {
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

	public static function delete_all_animals() {

		$delete_post = array(
			'post_type' => 'animal',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		);
		$query = new WP_Query($delete_post);
		$posts = $query->posts;
		if ($posts) {
			printf('<h4>count: %d</h4>', count($posts));

			foreach ($posts as $post) {
				//var_dump($post);
				//printf('<h3>end animal</h3>');
				self::delete_animal_post($post->ID);
			}

		}

	}

	/*
		 *
		 * returns attachment ID of either newly created image or attachment ID of existing image
		 *
	*/
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
			//printf('<h2>attachment exists</h2>');
			return $attachment_check;
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

	public function create_animal_post($animal) {
		//if (get_post_type($post_id) == 'animal') {

		$new_animal = array(
			'post_title' => $animal->Name,
			'post_type' => 'animal',
			'post_content' => $animal->Description,
			'post_status' => 'publish',
			'comment_status' => 'closed', // if you prefer
			'ping_status' => 'closed', // if you prefer
		);

		$post_id = get_page_by_title($new_animal['post_title'], OBJECT, 'animal');

		if (!$post_id) {

			if (PLUGIN_DEBUG) {
				printf('<h2>NEW ANIMAL</h2>');
				//print_r($animal);
				printf('<h5>Name %s</h5>', $animal->Name);
			}

			// CREATE A NEW ANIMAL POST AND UPDATE THE META FIELDS
			$new_post_id = wp_insert_post($new_animal);
			$blah = wp_set_object_terms(
				$new_post_id,
				array('0' => 'All Animal', '1' => $animal->Type),
				'adopt-animals'
			);

			if ($new_post_id) {

				// insert post meta
				$post_thumbnail = $this->upload_image($animal->CoverPhoto, $new_post_id);

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

				//printf('<h2 class="red_pet">Photos for %s</h2>', $animal->Name);
				//print_r($animal->Photos);
				foreach ($animal->Photos as $photo) {
					//printf('<h4>Add Photo: %s</h4>', $photo);
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
				$this->delete_animal_post($post_id->ID);
			} else {
				//printf('<h5>Status Match: %s</h5>', $animal->Name);
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
				//var_dump($post_thumbnail);
				//update_post_meta($post_id->ID, '_thumbnail_id', $post_thumbnail);
			}
		}
		return $post_id;
	} // END public function new_animal_post()

	public function animals_change_posts_per_page($query) {
		if (is_admin() || !$query->is_main_query()) {
			return;
		}

		if (is_post_type_archive('animal')) {
			$query->set('orderby', 'rand');
			$query->set('posts_per_page', -1);
		}
		if (is_tax('adopt-animals')) {
			$query->set('posts_per_page', -1);
			$query->set('orderby', 'rand');
		}
	}

	public function ghhs_archive_animal_template($template) {

		if (is_archive() && get_post_type() == 'animal') {

			if (file_exists(plugin_dir_path(__FILE__) . 'templates/taxonomy-adopt-animals.php')) {
				$archive_template = plugin_dir_path(__FILE__) . 'templates/taxonomy-adopt-animals.php';
			}
			return $archive_template;

		} else {
			return $template;
		}

	}
	public function ghhs_single_animal_template($template) {

		if (is_single() && get_post_type() == 'animal') {
			// Checks for single template by post type
			if (file_exists(plugin_dir_path(__FILE__) . 'templates/single-animal.php')) {

				$template = plugin_dir_path(__FILE__) . 'templates/single-animal.php';
				return $template;
			}

		} else {

			return $template;
		}
	}

	public function run() {
		//$attributes = string) {

		if (REMOVE_TRANSIENT) {
			$this->ghhs_remove_transient();
		}

		$this->request_uri = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable';
		//$number_requests = $this->query_number_animals($this->request_uri, $this->args);

		ob_start();

		$this->ghhs_pets_object = $this->request_and_sort($this->request_uri, $this->args);
		//$pets = $this->super_request($this->args);
		//print_r($pets_object);
		//print_r($this->petID_array);

		//$this->display_pets($pets_object, $animal_type, $print_mode);
		$this->create_and_update_animals($this->ghhs_pets_object);
		$this->delete_adopted_animals($this->petID_array);
		//$this->delete_all_animals();

		return ob_get_clean();

	}

} // end class definition

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
