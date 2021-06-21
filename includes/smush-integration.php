<?php

add_action('after_setup_theme', 'wpmudev_smpro_disable_cdn', 100);

function wpmudev_smpro_disable_cdn()
{
	if (defined('WP_SMUSH_VERSION') && class_exists('WP_Smush')) {
		class WPMUDEV_Smush_Disable_CDN
		{
			public $post_types = ['animal'];
			private $should_custom_url;
			public function __construct()
			{
				add_action('begin_fetch_post_thumbnail_html', array($this, 'should_custom_url'));

				add_action('end_fetch_post_thumbnail_html', array($this, 'disable_filter'));

				add_filter('wp_calculate_image_srcset', array($this, 'custom_srcset_disable_cdn'));

				add_filter('wp_get_attachment_image_src', array($this, 'custom_src_disable_cdn'));

				add_filter('smush_skip_image_from_cdn', array($this, 'maybe_skip_cdn'), 11, 2);
				add_filter('smush_cdn_skip_image', array($this, 'maybe_skip_cdn'), 11, 2);
			}

			public function maybe_skip_cdn($status, $src)
			{
				if (strpos($src, '?skip-cdn')) {
					$status = true;
				}
				return $status;
			}

			public function should_custom_url($post_id)
			{
				if (in_array(get_post_type($post_id), $this->post_types)) {
					$this->should_custom_url = 1;
				}
			}

			public function disable_filter()
			{
				if ($this->should_custom_url) {
					$this->should_custom_url = null;
				}
			}

			public function custom_src_disable_cdn($image)
			{
				if ($this->should_custom_url && isset($image[0])) {
					$image[0] .= '?skip-cdn';
				}
				return $image;
			}

			public function custom_srcset_disable_cdn($sources)
			{
				if ($this->should_custom_url && $sources) {
					foreach ($sources as $size => $source) {
						$source['url'] .= '?skip-cdn';
						$sources[$size] = $source;
					}
				}
				return $sources;
			}
		}

		$run = new WPMUDEV_Smush_Disable_CDN();
	}
}

/**
 * For integration with Smush CDN - don't cache our animal images!
 */
// function ghhs_smush_integration($status, $src)
// {
// 	if (is_archive() && get_post_type() == 'animal') {
// 		return $status;
// 	}
// 	static $thumbnail_url;
// 	if (is_null($thumbnail_url)) {
// 		//		if( is_singular( 'animal' ) || has_term('adopt-animals')) {
// 		if (is_singular('animal')) {

// 			$img = get_the_post_thumbnail_url(get_queried_object_id());
// 			//echo $img;
// 			if ($img) {
// 				$thumbnail_url = substr($img, 0, strrpos($img, '.'));
// 			} else {
// 				$thumbnail_url = false;
// 			}
// 		} else {
// 			$thumbnail_url = false;
// 		}
// 	}
// 	if ($thumbnail_url && 0 === strpos($src, $thumbnail_url)) {
// 		//printf('<p>Thumb URL - %s</p>', $thumbnail_url);

// 		return true;
// 	}

// 	return $status;
// }
// add_filter('smush_skip_image_from_cdn', 'ghhs_smush_integration', 11, 2);
// add_filter('smush_cdn_skip_image', 'ghhs_smush_integration', 11, 2);

// function ghhs_smush_integration_archive($status, $src, $image)
// {

// 	if (!is_archive() && !get_post_type() == 'animal') {
// 		return $status;
// 	}

// 	$args = array(
// 		'post_type' => 'animal',
// 		'post_status' => 'publish',
// 		'posts_per_page' => -1,
// 	);

// 	$animal_images = array();

// 	$query = new WP_Query($args);

// 	if ($query->have_posts()) {
// 		$posts = $query->posts;
// 		foreach ($posts as $post) {
// 			$img = get_the_post_thumbnail_url($post->ID);
// 			$thumbnail_url = substr($img, 0, strrpos($img, '.'));
// 			$animal_images[] = $thumbnail_url;
// 		}
// 	}

// 	foreach ($animal_images as $img) {
// 		if ($img && 0 === strpos($src, $img)) {
// 			return true;
// 		}
// 	}

// 	return $status;
// }

// add_filter('smush_skip_image_from_cdn', 'ghhs_smush_integration_archive', 11, 3);
// add_filter('smush_cdn_skip_image', 'ghhs_smush_integration_archive', 11, 3);
