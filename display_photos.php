<?php
/**
 * 
 * This file display multiple photos of an animal API request made in the found_pets_shortcode
 *
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

ob_start();

include_once ('include_styles.php');

function display_photos( $pet )
{
	
?>

     <!-- Swiper -->
  <!--div class="swiper-container">
    <div class="swiper-wrapper"-->
<div data-featherlight-gallery data-featherlight-filter="img">

<?php

    		foreach ($pet->Photos as $photo) {
    			?>
              
              <img class="gallery animal-pic" style="" src="<?php echo $photo;?>">
 
          
    			<!--div class="swiper-slide" style="background-image: url(<?php echo $photo; ?>);"></div-->

<?php	
    		}
      ?>
   </div>
  


<?php


}
ob_clean();
?>