<?php

/**
 *
 * This file displays the content of an API request made in the found_pets_shortcode
 *
 *
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class GHHS_Animals_Slideshow
{
	public function __construct()
	{
	}

	public function print_opening_html()
	{

		if (PLUGIN_DEBUG) {
			echo "<h2>printing slideshow</h2>";
		}
		echo '<div id="petCarousel" class="carousel slide">';
		echo '<div class="carousel-inner mx-auto">';
	}

	public function print_closing_html()
	{
?>
		</div> <!-- end carousel-inner -->


		<!-- Carousel controls
    <a class="carousel-control-prev" href="#petCarousel" data-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </a>
    <a class="carousel-control-next" href="#petCarousel" data-slide="next">
        <span class="carousel-control-next-icon"></span>
    </a> -->
		</div>

		<script language="JavaScript" type="text/javascript">
			jQuery(function($) {
				$(document).ready(function() {
					$('.carousel').carousel({
						interval: 5000
					});
				});
			});
		</script>
<?php
	} // end print_closing_html()

	public function run()
	{
		$args = array(
			'post_type' => 'animal',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'order' => 'random',
		);

		$animals = new WP_Query($args);
		if ($animals->have_posts()) {

			$this->print_opening_html();
			$i = 0;
			while ($animals->have_posts()) {
				$animals->the_post();

				if ($i == 0) {
					printf('<div class="carousel-item active" style="height: 800px;">');
					printf('<img class="d-block w-100 mx-auto img-fluid" src="%s" alt="%s">', get_the_post_thumbnail_url($animals->post, 'large'), get_the_title());
					printf('<div class="carousel-caption d-none d-md-block">');
					printf('<h3 style="color:#ffffff;">%s</h3></div>', get_the_title());
					printf('</div>');
					$i++;
				} else {
					printf('<div class="carousel-item" style="height: 800px;">');
					printf('<img class="d-block w-100 mx-auto img-fluid" src="%s" alt="%s">', get_the_post_thumbnail_url($animals->post, 'large'), get_the_title());
					printf('<div class="carousel-caption d-none d-md-block">');
					printf('<h3 style="color:#ffffff;">%s</h3></div>', get_the_title());
					printf('</div>');
				}
			}
			$this->print_closing_html();
		} else {
			printf('<h2>Could not retrieve animal posts. No data to display.</h2>');
			return;
		}
	}
}
