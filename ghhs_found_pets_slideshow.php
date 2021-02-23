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

//include 'display_photos.php';

class Ghhs_Found_Pets_Slideshow {
	public function __contsruct() {}

	public function randomize_pets($pets) {

		return shuffle($pets);
	}
	public function display($pets) {

		//$newpets = array_merge($pets['cats'] + $pets['dogs'] + $pets['others']);
		//$newpets = $this->randomize_pets($pets);

		if (PLUGIN_DEBUG) {
			echo "<h2>printing slideshow</h2>";
		}

		$pet = NULL;

		echo '<!-- Slider main container -->';
		echo '<div class="swiper-container-pets">';
		echo '<!-- Additional required wrapper -->';
		echo '<div class="swiper-wrapper">';

		foreach ($pets as $pet) {

			echo '<div class="swiper-slide-pets swiper-slide" style="background-image:url(' . $pet->CoverPhoto . ');">';
			//echo '<div class="swiper-slide">';
			//echo '<img src="' . $pet->CoverPhoto . '" style="height:80vh;" />';
			echo '<a href="' . ANIMAL_LINK . $pet->ID . '" target="_blank">' . $pet->Name . '</a>';
			echo '</div>';

		}

		echo '</div><!-- If we need pagination --><div class="swiper-pagination"></div>

			<!-- If we need navigation buttons
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
			-->

			<!-- If we need scrollbar
			<div class="swiper-scrollbar"></div>
			-->
		</div>';
		?>
		<script>
		const swiper = new Swiper('.swiper-container', {
 			// Optional parameters
  			direction: 'horizontal',
 			loop: true,
 			slidesPerView: 1,
      		spaceBetween: 10,
      		effect: 'fade',
      		autoplay: {
        		delay: 4500,
        		disableOnInteraction: true,
      			},


 			// If we need pagination
  			pagination: {
  				el: '.swiper-pagination',
  			},

  			// Navigation arrows
  			navigation: {
  				nextEl: '.swiper-button-next',
  				prevEl: '.swiper-button-prev',
  			},

  			// And if we need scrollbar
  			scrollbar: {
  				el: '.swiper-scrollbar',
  			},
		});
		</script>


<?php
}

}