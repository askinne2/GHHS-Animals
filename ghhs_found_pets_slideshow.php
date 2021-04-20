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

class Ghhs_Found_Pets_Slideshow {
	public function __construct() {}

	public function display_all_pictures($pets) {

		//$newpets = array_merge($pets['cats'] + $pets['dogs'] + $pets['others']);
		//$newpets = $this->randomize_pets($pets);

		if (PLUGIN_DEBUG) {
			echo "<h2>printing slideshow</h2>";
		}

		?>
    <div id="petCarousel" class="h-50 carousel slide" data-interval="3000" data-ride="carousel">
		<div class="carousel-inner h-100 mx-auto">

      <?php

		$i = 0;
		foreach ($pets as $pet) {

			if ($i == 0) {
				printf('<div class="carousel-item active">');
				printf('<img class="d-block mx-auto img-fluid" src="%s" alt="%s">', $pet->CoverPhoto, $pet->Name);
				printf('</div>');
				$i++;
			} else {
				printf('<div class="carousel-item">');
				printf('<img class="d-block mx-auto img-fluid" src="%s" alt="%s">', $pet->CoverPhoto, $pet->Name);
				printf('</div>');
			}
		}
		?>
		</div> <!-- end carousel-inner -->


    <!-- Carousel controls -->
    <a class="carousel-control-prev" href="#petCarousel" data-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </a>
    <a class="carousel-control-next" href="#petCarousel" data-slide="next">
        <span class="carousel-control-next-icon"></span>
    </a>
  </div>

  <script language="JavaScript" type="text/javascript">
    jQuery(function($) {
  $(document).ready(function(){
    $('.carousel').carousel({
      interval: 2000
    });
  });
});
</script>


    <?php

	}

}