<?php
/**
 * For integration with Smush CDN - don't cache our animal images!
 */

function ghhs_smush_integration($status, $src, $image) {

	$args = array(
		'post_type' => 'animal',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	);

	$animal_images = array();

	$query = new WP_Query($args);
	if ($query->have_posts()) {
		$posts = $query->posts;
		foreach ($posts as $post) {
			$animal_images[] = get_the_post_thumbnail_url($post->ID);
		}

	}
	foreach ($animal_images as $url) {

		if ($src == $url) {
			if (PLUGIN_DEBUG) {
				printf('<h4>excluding: %s</h4>', $url);
			}

			return true;

		}

	}

}

add_filter('smush_skip_image_from_cdn', 'ghhs_smush_integration', 10, 3);