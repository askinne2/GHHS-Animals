<?php
/**
 * @link              https://github.com/askinne2/GHHS-Animals
 * @since             1.0.0
 * @package           [ghhs_animals]
 *
 * @wordpress-plugin
 * Plugin Name:       GHHS Animals
 * Plugin URI:        https://github.com/askinne2/GHHS-Animals
 *
 * Description:       Displays all animals that are currently listed in Greater Huntsville Humane Society's database in Shelterluv on website.
 *
 *
 * Version:           2.3.0
 * Author:            Andrew Skinner
 * Author URI:        https://www.21adsmedia.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ghhs_animals
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/askinne2/GHHS-Animals

 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

define('PLUGIN_DEBUG', true);
define('REMOVE_TRANSIENT', true);
define('LOCAL_JSON', false);
define('GHHS_UPLOADS', 'wp-content/uploads/ghhs-animals');
define('ADOPT_LINK', 'https://www.shelterluv.com/matchme/adopt/ghhs-a-');

require_once 'ghhs_animals_includes.php';
require_once 'ghhs_animals_slideshow.php';
require_once 'ghhs_animals_acf.php';

class GHHS_Animals {

	const CRON_HOOK = 'ghhs_update_animals';

	var $request_uri;
	var $args;
	var $ghhs_animals;
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

		// register Custom Post Type 'Animal' and Taxonomy 'Adopt-Animals'
		$this->ghhs_animals = new GHHS_Animals_ACF();

		// hook into custom post type actions and filters
		add_action('trashed_post', array($this, 'delete_animal_post'));
		add_filter('pre_get_posts', array($this, 'animals_change_posts_per_page'));
		add_filter('template_include', array($this, 'ghhs_single_animal_template'), 9999);
		add_filter('template_include', array($this, 'ghhs_archive_animal_template'), 9999);

		// run the GHHS_Animals() program
		add_action(self::CRON_HOOK, array($this, 'run'));
		//add_shortcode('GHHS_Animals', array($this, 'run'));
		add_shortcode('GHHS_Animals_Update', array($this, 'run_update'));

		// functionality added for shortcode [ghhs_slideshow]
		add_shortcode('ghhs_slideshow', array($this, 'display_pets'));
	}

	/**
	 * Hook into the WordPress activate hook
	 */
	public static function activate() {

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

	public function make_local_request() {

		$transient = get_transient('ghhs_pets');
		if (!empty($transient)) {
			if (PLUGIN_DEBUG) {
				printf('<h2 class="red_pet">TRANSIENT FOUND</h2>');
				//printf('<pre>');
				//print_r($transient);
				//printf('</pre>');
			}
			return $transient;

		} else {
			$jsonpets = file_get_contents(plugins_url(plugin_basename(__DIR__)) . '/example-animal-json/large1.json');
			$pets = json_decode($jsonpets);
			if (empty($pets)) {
				if (PLUGIN_DEBUG) {
					print('<h4>No JSON to decode</h4>');
				}
				return;
			} else {
				if ($pets->total_count < 100) {

					// low animal count, we can just set transient and return animals 0 -> 100.
					if (PLUGIN_DEBUG) {
						printf('<h2 class="red_pet">SET TRANSIENT LOW ANIMALS</h2>');
					}
					set_transient('ghhs_pets', $pets->animals, HOUR_IN_SECONDS);
					return $pets->animals;

				} else {

					if (PLUGIN_DEBUG) {
						printf('<h2>multiple requests</h2>');
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

					$jsonpets = array();
					$all_pets = array();

					// Build our array of request URI's and make more calls
					for ($i = 0; $i < $total_requests; $i++) {
						$request_uri[$i] = plugins_url(plugin_basename(__DIR__)) . '/example-animal-json/large' . ($i + 1) . '.json';

						if (PLUGIN_DEBUG) {
							echo "<p>fetching - " . $request_uri[$i] . "</p>";
							printf('<p>i - %s,', $i);
						}
						$jsonpets[$i] = file_get_contents($request_uri[$i]);
						if (!$jsonpets[$i]) {
							printf('<h2>No file_get_contents(), $jsonpet[%d]</h2>', $i);
						} else {

							$all_pets[] = json_decode($jsonpets[$i])->animals;

							$animals = call_user_func_array('array_merge', $all_pets);

						}

					}

					if (empty($animals)) {
						if (PLUGIN_DEBUG) {
							echo "<p>make_request(): No pets to json_decode </p>";
						}
						return;
					} else {

						if (PLUGIN_DEBUG) {
							printf('<h2 class="red_pet">SET TRANSIENT 100+ ANIMALS</h2>');
						}
						set_transient('ghhs_pets', $animals, HOUR_IN_SECONDS);
						return $animals;
					}

				} // end multiple request check
			} // end empty(pets) check
		} // end transient check

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
			return $transient;

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

			if (empty($pets)) {
				if (PLUGIN_DEBUG) {
					print('No JSON to decode');
				}

				return;
			}

			if ($pets->total_count < 100) {

				// low animal count, we can just set transient and return animals 0 -> 100.
				if (PLUGIN_DEBUG) {
					printf('<h2 class="red_pet">SET TRANSIENT LOW ANIMALS</h2>');
				}
				set_transient('ghhs_pets', $pets->animals, HOUR_IN_SECONDS);
				return $pets->animals;

			} else {

				if (PLUGIN_DEBUG) {
					printf('<h2>multiple requests</h2>');
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

				$jsonpets = array();
				$all_pets = array();

				// Build our array of request URI's and make more calls
				for ($i = 0; $i < $total_requests; $i++) {
					$request_uri[$i] = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable&offset=' . $i . '00&limit=100';
					if (PLUGIN_DEBUG) {
						echo "<p>fetching - " . $request_uri[$i] . "</p>";
						printf('<p>i - %s,', $i);
					}

					$jsonpets[$i] = wp_remote_get($request_uri[$i], $this->args);
					if (is_wp_error($jsonpets[$i]) || '200' != wp_remote_retrieve_response_code($jsonpets[$i])) {
						if (PLUGIN_DEBUG) {
							echo "<p>Bad wp_remote_get Request. in Multiple Request. </p>";
						}
						return;

					} else {
						$all_pets[] = json_decode(wp_remote_retrieve_body($jsonpets[$i]))->animals;
						$animals = call_user_func_array('array_merge', $all_pets);

					}

					if (empty($animals)) {
						if (PLUGIN_DEBUG) {
							echo "<p>make_request(): No pets to json_decode </p>";
						}
						return;
					} else {

						if (PLUGIN_DEBUG) {
							printf('<h2 class="red_pet">SET TRANSIENT 100+ ANIMALS</h2>');
						}
						set_transient('ghhs_pets', $animals, HOUR_IN_SECONDS);
						return $animals;
					}
				} // end multiple request check

			} // end empty(pets) check
		} // end transient check

	} // end make_request()

	public function ghhs_remove_transient() {
		delete_transient('ghhs_pets');
	}

	public function request_and_sort($request_uri = string, $args = array()) {

		if (LOCAL_JSON) {
			$pets = $this->make_local_request();
			if (empty($pets)) {
				echo "<h5>Uh oh. Our shelter is experiencing technical difficulties.</h5>";
				echo "<p>Please email <a href=\"mailto:info@ghhs.org\">info@ghhs.org</a> to let them know about the problem you have experienced. We apologize and will fix the issue ASAP.</p>";
				return;
			}

		} else {

			$pets = $this->make_request($request_uri, $args);
			if (empty($pets)) {
				echo "<h5>Uh oh. Our shelter is experiencing technical difficulties.</h5>";
				echo "<p>Please email <a href=\"mailto:info@ghhs.org\">info@ghhs.org</a> to let them know about the problem you have experienced. We apologize and will fix the issue ASAP.</p>";
				return;
			}
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

	public function check_animal_exists($animal) {
		$args = array(
			'post_type' => 'animal',
			'meta_query' => array(
				array(
					'key' => 'animal_id',
					'value' => $animal->ID,
					'compare' => '=',
				),
			),
		);
		$query = new WP_Query($args);
		if ($query->have_posts()) {
			$posts = $query->posts;
			return $posts[0];
		} else {
			return false;
		}

	} // end check_animal_exists()

	public function create_and_update_animals($pets_object) {

		$dogs = $pets_object['dogs'];
		$cats = $pets_object['cats'];
		$others = $pets_object['others'];

		foreach ($dogs as $dog) {

			$exists = $this->check_animal_exists($dog);

			if (!$exists) {
				$postid = $this->create_animal_post($dog);
				if (!$postid) {
					if (PLUGIN_DEBUG) {
						printf('<h2>Failed to create %s</h2>', $dog->Name);
					}
				}
			} else {
				$pid = $this->update_animal($dog, $exists);
				if (!$pid) {

					if (PLUGIN_DEBUG) {
						printf('<h2>Failed to create %s</h2>', $dog->Name);
					}
				}
			}
		} //end foreach dogs loop
		foreach ($cats as $cat) {

			$exists = $this->check_animal_exists($cat);

			if (!$exists) {
				$postid = $this->create_animal_post($cat);
				if (!$postid) {
					if (PLUGIN_DEBUG) {
						printf('<h2>Failed to create %s</h2>', $cat->Name);
					}
				}
			} else {
				$pid = $this->update_animal($cat, $exists);
				if (!$pid) {

					if (PLUGIN_DEBUG) {
						printf('<h2>Failed to create %s</h2>', $cat->Name);
					}
				}
			}
		} //end foreach cats loop
		foreach ($others as $other) {

			$exists = $this->check_animal_exists($other);

			if (!$exists) {
				$postid = $this->create_animal_post($other);
				if (!$postid) {
					if (PLUGIN_DEBUG) {
						printf('<h2>Failed to create %s</h2>', $other->Name);
					}
				}
			} else {
				$pid = $this->update_animal($other, $exists);
				if (!$pid) {

					if (PLUGIN_DEBUG) {
						printf('<h2>Failed to create %s</h2>', $other->Name);
					}
				}
			}
		} //end foreach others loop
	}

	public function display_pets() {
		$pet_slideshow = new GHHS_Animals_Slideshow();
		$pet_slideshow->run();
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
				if (PLUGIN_DEBUG) {
					printf('<h2> Delete this animal: %d</h2>', $id, $animal_ids->wp_id[$i]);
				}
				$this->delete_animal_post($post_to_delete);
			}
			$i++;

		}
	}

	public static function delete_animal_post($post_id) {
		if (get_post_type($post_id) == 'animal') {
			// <-- members type posts
			// Force delete
			if (PLUGIN_DEBUG) {
				printf('<h2>post id:</h2>');
				print_r($post_id);
			}
			$post_attachments = get_attached_media('', $post_id);

			if (PLUGIN_DEBUG) {
				printf('<h3>post attachments:</h3>');
				print_r($post_attachments);
			}
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
			if (PLUGIN_DEBUG) {printf('<h4>count: %d</h4>', count($posts));}

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
	public function upload_image($url, $name, $post_id, $animal_id) {
		// Add Featured Image to Post
		$image_url = $url; // Define the image URL here
		$image_name = 'animal-' . $name . '-' . $animal_id . '.png';
		$upload_dir = wp_upload_dir(); // Set upload folder

		// check if images exists
		$images = get_attached_media('image', $post_id);

		if (!empty($images)) {
			if (PLUGIN_DEBUG) {
				printf('<h2>attachment exists<h2>');
			}
			foreach ($images as $image) {

				wp_delete_attachment($image->ID, true);

			}
		}
		$image_data = file_get_contents($image_url); // Get image data
		$unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
		if (PLUGIN_DEBUG) {
			printf('<h4 class="red_pet">unique_file_name: %s</h4>', $unique_file_name);
		}
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

	public function create_animal_post($animal) {

		$new_animal = array(
			'post_title' => $animal->Name,
			'post_type' => 'animal',
			'post_content' => $animal->Description,
			'post_status' => 'publish',
			'comment_status' => 'closed', // if you prefer
			'ping_status' => 'closed', // if you prefer
		);

		// CREATE A NEW ANIMAL POST AND UPDATE THE META FIELDS
		$new_post_id = wp_insert_post($new_animal);
		if ($new_post_id) {

			if (strcmp($animal->Type, 'Dog') === 0) {
				$blah = wp_set_object_terms(
					$new_post_id,
					array('0' => 'All Animal', '1' => 'Dog'),
					'adopt-animals'
				);
			} else if (strcmp($animal->Type, 'Cat') === 0) {
				$blah = wp_set_object_terms(
					$new_post_id,
					array('0' => 'All Animal', '1' => 'Cat'),
					'adopt-animals'
				);
			} else {
				$blah = wp_set_object_terms(
					$new_post_id,
					array('0' => 'All Animal', '1' => 'Other'),
					'adopt-animals'
				);
			}

			// insert post meta
			$post_thumbnail = $this->upload_image($animal->CoverPhoto, $animal->Name, $new_post_id, $animal->ID);

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

			$adopt_link = ADOPT_LINK . $animal->ID;
			add_post_meta($new_post_id, 'adopt_link', $adopt_link);

			//printf('<h2 class="red_pet">Photos for %s</h2>', $animal->Name);
			//print_r($animal->Photos);
			foreach ($animal->Photos as $photo) {
				//printf('<h4>Add Photo: %s</h4>', $photo);
				add_post_meta($new_post_id, 'photos', $photo);
			}

		} else {

			if (PLUGIN_DEBUG) {
				printf('<h2>insert post failed for %s</h2>', $animal->Name);
				return NULL;
			}
		}

		return $new_post_id;
	} // END public function new_animal_post()

	public function update_animal($animal, $post_id) {

		//$post_id = get_page_by_title($animal->Name, OBJECT, 'animal');

		// CHECK IF ANIMAL NEEDS TO BE DELETED BY SHELTERLUV STATUS
		$animal_status = get_post_meta($post_id->ID, 'status', true);

		if (!in_array($animal_status, $this->status_array)) {
			if (PLUGIN_DEBUG) {
				printf('<h5 class="red_pet">Deleting Animal: %s</h5>', $animal->Name);
			}
			$this->delete_animal_post($post_id->ID);

		} else {
			if (PLUGIN_DEBUG) {
				printf('<h4 class="red_pet">Checking Update Animal: %s</h4>', $animal->Name);
			}
		}

		$postUpdateTime = get_post_timestamp($post_id->ID);
		if (PLUGIN_DEBUG) {

			printf('<h4>Post Update Time: %s </h4>', $postUpdateTime);
			printf('<h4>ShelterLuv Update Time: %s</h4>', $animal->LastUpdatedUnixTime);

		}

		// ONLY UPDATE IF THE SHELTERLUV TIMESTAMP IS NEWER THAN POST TIME
		if ($animal->LastUpdatedUnixTime >= $postUpdateTime) {

			if (PLUGIN_DEBUG) {
				printf('<h4 class="red_pet">UPDATE ANIMAL: %s, POST ID: %s</h4>', $animal->Name, $post_id->ID);
				//print_r($animal);
				printf('<h5>ID: %s</h5><h4>New Photo</h4><h4>cover URL: %s</h4>', $animal->ID, $animal->CoverPhoto);
			}

			// insert post meta
			$post_thumbnail = $this->upload_image($animal->CoverPhoto, $animal->Name, $post_id->ID, $animal->ID);

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
			if (isset($animal->AdoptionFeeGroup->Price)) {
				add_post_meta($post_id->ID, 'adoption_fee', $animal->AdoptionFeeGroup->Price);
			}

			$adopt_link = ADOPT_LINK . $animal->ID;
			update_post_meta($post_id->ID, 'adopt_link', $adopt_link);
			delete_post_meta($post_id->ID, 'photos');
			foreach ($animal->Photos as $photo) {
				add_post_meta($post_id->ID, 'photos', $photo);
			}

			$update_animal_post = array(
				'ID' => $post_id->ID,
				'post_title' => $animal->Name,
				'post_type' => 'animal',
				'post_content' => $animal->Description,
				'post_status' => 'publish',
				'comment_status' => 'closed', // if you prefer
				'ping_status' => 'closed', // if you prefer
				'post_modified' => time(),
			);

			$post_id = wp_insert_post($update_animal_post, true);
			if (is_wp_error($post_id) || $post_id == 0) {
				if (PLUGIN_DEBUG) {
					print('wp_error:');
					print_r($post_id);
				}

			}

			$postUpdateTime = get_post_timestamp($post_id);

			if (PLUGIN_DEBUG) {
				printf('<h4>after call to wp_update_post()</h4>');
				printf('<h4>Post Update Time: %s </h4>', $postUpdateTime);
				printf('<h4>ShelterLuv Update Time: %s</h4>', $animal->LastUpdatedUnixTime);

			}

		} // end timestamp comparison
		return $post_id;
	} // end update_animal()

	public function check_duplicate_animals() {

		/* basic logic:
			 * get all animal posts...
			 * check if animal->ID is in any two places
			 * delete if oldest shelterluv update time
			 *
		*/

		$args = array(
			'post_type' => 'animal',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		);

		$animal_ids = array();
		$query = new WP_Query($args);
		if ($query->have_posts()) {
			$posts = $query->posts;
			foreach ($posts as $index => $post) {
				$animal_ids[$index] = get_field('animal_id', $post->ID);
			}
		}

		if (PLUGIN_DEBUG) {
			printf('<h4 class="red_pet">check duplicates, </h4>');
			print_r($animal_ids);
		}

		foreach ($animal_ids as $aid) {
			$args = array(
				'post_type' => 'animal',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => 'animal_id',
						'value' => $aid,
						'compare' => '=',
					),
				),
			);

			$query = new WP_Query($args);
			if ($query->have_posts()) {
				if ($query->found_posts > 1) {
					if (PLUGIN_DEBUG) {
						printf('<h2 class="red_pet">DUPLICATE, aid - %s</h2>', $aid);
					}
					foreach ($query->posts as $i => $post) {
						if (PLUGIN_DEBUG) {
							printf('<h4>' . get_field('last_update_time', $post->ID) . '</h4>');
							printf('<h4> second one: ' . get_field('last_update_time', $query->posts[$i + 1]->ID) . '</h4>');
						}

						$posttime = get_field('last_update_time', $post->ID);
						$nextposttime = get_field('last_update_time', $query->posts[$i + 1]->ID);
						if ($posttime < $nextposttime) {
							$this->delete_animal_post($post->ID);
							break;
						} else if ($posttime > $nextposttime) {
							$this->delete_animal_post($query->posts[$i + 1]->ID);
							break;
						} else if ($posttime == $nextposttime) {
							$this->delete_animal_post($post->ID);
							break;
						}

					}

				}
			}
		}

	} // END check_duplicate_animals()

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

		if (REMOVE_TRANSIENT) {
			$this->ghhs_remove_transient();
		}

		$this->request_uri = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable';

		ob_start();

		$this->ghhs_pets_object = $this->request_and_sort($this->request_uri, $this->args);
		$this->create_and_update_animals($this->ghhs_pets_object);
		$this->delete_adopted_animals($this->petID_array);
		$this->check_duplicate_animals();

		return ob_get_clean();

	}
	public function run_update() {

		if (REMOVE_TRANSIENT) {
			$this->ghhs_remove_transient();
		}

		$this->request_uri = 'https://www.shelterluv.com/api/v1/animals/?status_type=publishable';
		//$this->delete_all_animals();

		ob_start();

		$this->ghhs_pets_object = $this->request_and_sort($this->request_uri, $this->args);
		$this->create_and_update_animals($this->ghhs_pets_object);
		$this->delete_adopted_animals($this->petID_array);
		$this->check_duplicate_animals();

		return ob_get_clean();

	}

} // end class definition

// Installation and uninstallation hooks
register_activation_hook(__FILE__, array('GHHS_Animals', 'activate'));
register_deactivation_hook(__FILE__, array('GHHS_Animals', 'deactivate'));

// run GHHS_Animals shortcode

function custom_http_request_timeout() {
	return 15;
}
add_filter('http_request_timeout', 'custom_http_request_timeout');

$pets = new GHHS_Animals();
