<?

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

function ghhs_animal_template($single) {

	global $post;

	/* Checks for single template by post type */
	if ($post->post_type == 'animal') {
		if (file_exists(plugin_dir_path(__FILE__) . 'single-animal.php')) {

			$single = plugin_dir_path(__FILE__) . 'single-animal.php';
		} else {
			echo "<h2>fuck</h2>";
		}

	}

	return $single;
}

/* Filter the single_template with our custom function*/
add_filter('single_template', 'ghhs_animal_template');
