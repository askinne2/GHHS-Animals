<?php

/**
 * For integration with Smush CDN - don't cache our animal images!
 */
function ghhs_smush_integration($status, $src)
{
	if (is_archive() && get_post_type() == 'animal') {
		return $status;
	}
	static $thumbnail_url;
	if (is_null($thumbnail_url)) {
		//		if( is_singular( 'animal' ) || has_term('adopt-animals')) {
		if (is_singular('animal')) {

			$img = get_the_post_thumbnail_url(get_queried_object_id());
			//echo $img;
			if ($img) {
				$thumbnail_url = substr($img, 0, strrpos($img, '.'));
			} else {
				$thumbnail_url = false;
			}
		} else {
			$thumbnail_url = false;
		}
	}
	if ($thumbnail_url && 0 === strpos($src, $thumbnail_url)) {
		//printf('<p>Thumb URL - %s</p>', $thumbnail_url);

		return true;
	}

	return $status;
}
add_filter('smush_skip_image_from_cdn', 'ghhs_smush_integration', 11, 2);
add_filter('smush_cdn_skip_image', 'ghhs_smush_integration', 11, 2);

function ghhs_smush_integration_archive($status, $src, $image)
{

	if (!is_archive() && !get_post_type() == 'animal') {
		return $status;
	}

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
			$img = get_the_post_thumbnail_url($post->ID);
			$thumbnail_url = substr($img, 0, strrpos($img, '.'));
			$animal_images[] = $thumbnail_url;
		}
	}

	foreach ($animal_images as $img) {
		if ($img && 0 === strpos($src, $img)) {
			return true;
		}
	}

	return $status;
}

add_filter('smush_skip_image_from_cdn', 'ghhs_smush_integration_archive', 11, 3);
add_filter('smush_cdn_skip_image', 'ghhs_smush_integration_archive', 11, 3);
