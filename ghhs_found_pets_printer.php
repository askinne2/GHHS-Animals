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

define('ANIMAL_LINK', 'https://www.shelterluv.com/matchme/adopt/ghhs-a-');
define('DONATE_LINK', 'https://www.ghhs.org/donate');

class Ghhs_Found_Pets_Printer {

	public function __contsruct() {}

	public function print_section_opening_html() {

		?>
<div class="elementor-container elementor-column-gap-default">
   <div class="elementor-row">
      <?php
}

	public function print_section_closing_html() {
		?>
   </div>
</div>
<?php
}
	public function display_photos($pet) {

		echo '<div data-featherlight-gallery data-featherlight-filter="img">';

		foreach ($pet->Photos as $photo) {
			echo '<img class="gallery animal-pic" style="" src="' . $photo . '">';
		}

		echo '</div>';
	}

	public function display_animal($pet) {
		$adoption_link = ANIMAL_LINK . $pet->ID;

		?>
<div class="elementor-element elementor-element-def3b98 elementor-column elementor-col-33 elementor-top-column" data-id="def3b98" data-element_type="column">
   <div class="elementor-column-wrap  elementor-element-populated">
      <div class="elementor-widget-wrap animal">
         <div class="elementor-element elementor-element-5048a1c elementor-cta--skin-classic elementor-animated-content elementor-bg-transform elementor-bg-transform-zoom-in elementor-widget elementor-widget-call-to-action" data-id="5048a1c" data-element_type="widget" data-widget_type="call-to-action.default">
            <div class="elementor-widget-container">
               <div class="elementor-cta__bg-wrapper">
                  <a href="#" data-featherlight="#<?php echo $pet->ID; ?>">
                     <div class="elementor-cta__bg elementor-bg" style="background-image: url(<?php echo $pet->CoverPhoto; ?>);"></div>
                     <div class="elementor-cta__bg-overlay"></div>
                  </a>
               </div>
               <div class="elementor-cta__content">
                  <h2 class="elementor-cta__title elementor-cta__content-item elementor-content-item" style="text-align: center;"> <?php echo $pet->Name; ?></h2>
                  <div class="elementor-cta__description elementor-cta__content-item elementor-content-item">
                     <p class="animal-details">
                        <?php echo $pet->Color . ' ' . $pet->Sex . ' ' . $pet->Type; ?>
                     </p>
                     <p class="animal-breed">Breed:
                        <br />
                        <?php echo $pet->Breed; ?>
                     </p>
                     <p class="animal-age">Status:
                        <?php echo $pet->Status; ?>
                        </br>
                     </p>
                     <p class="animal-age">Age:     <?php echo number_format($pet->Age / 12, 1, ' years, ', ''); ?> months
                     </p>
                     <div class="elementor-cta__button-wrapper elementor-cta__content-item elementor-content-item adopt-button" style="text-align: center;">
                        <a href="#" data-featherlight="#<?php echo $pet->ID; ?>" class="elementor-button-link elementor-button elementor-size-lg" role="button">
                        <span class="elementor-button-content-wrapper">
                        <span class="elementor-button-text">More Pics & Bio</span>
                        </span>
                        </a>
                     </div>
                     <div id="<?php echo $pet->ID; ?>" class="animal-description">
                        <?php
$this->display_photos($pet);
		?>
                        <div class="elementor-cta__button-wrapper elementor-cta__content-item elementor-content-item adopt-button" style="text-align: center;">
                           <a href="<?php echo $adoption_link; ?>"  target="_blank" class="elementor-button-link elementor-button elementor-size-lg" role="button">
                           <span class="elementor-button-content-wrapper">
                           <span class="elementor-button-text">Adopt <?php echo $pet->Name; ?></span>
                           </span>
                           </a>
                        </div>
                        <?php
if (!empty($pet->Description)) {

			echo "<h5>" . $pet->Description . "</h5>";
		} else {
			echo "Sorry! I'm currently putting paw and pen together writing my autobiography!";
		}

		?>
                     </div>
                  </div>
                  <div class="elementor-cta__button-wrapper elementor-cta__content-item elementor-content-item adopt-button" style="text-align: center;">
                     <a href="#" data-featherlight="#<?php echo $pet->ID . 'adopt'; ?>" class="elementor-button-link elementor-button elementor-size-lg" role="button">
                        <!--a href="<?php echo $adoption_link; ?>"  target="_blank" class="elementor-button-link elementor-button elementor-size-lg" role="button"-->
                        <span class="elementor-button-content-wrapper">
                        <span class="elementor-button-text">Adopt</span>
                        </span>
                     </a>
                  </div>
                  <div id="<?php echo $pet->ID . 'adopt'; ?>" class="animal-description" style="text-align: left;">
                     <h3 >Our mission is to complete families through a thoughtful and thorough adoption process.</h3>
                     <p> To ensure every animal is placed in a forever home, GHHS begins this process with a detailed adoption application. You will fill out the application after choosing an animal to adopt.  To be considered for adoption:</p>
                     <ul>
                        <li>You must be at least 19 years old.</li>
                        <li>Your current pets must be spayed/neutered and up to date on all shots. Veterinarian references will be checked.</li>
                        <li>If you rent housing, you must have written permission from your landlord and proof of pet deposit. (<a href="http://www.zillow.com/huntsville-al/pet-friendly/" target="_blank" rel="noopener noreferrer">Click here</a> if you're looking for pet-friendly housing in Huntsville.)</li>
                        <li>If adopting into a family, we require all members of the family (including current dogs) to meet the animal on GHHS premises. This also means that animals cannot be adopted as "surprises" or "presents."</li>
                        <li>Some dogs may require a home inspection.</li>
                     </ul>
                     <p><b>Meeting these guidelines is not a guarantee that your application will be accepted. GHHS reserves the right to adopt only to qualified homes based upon our guidelines. Each adoption is considered on a first-come, first-qualified basis once the animal is available for adoption. Exceptions may be made for potential adopters.</b></p>
                     <h3>Adoption Fees</h3>
                     <p>Our adoption fees start at <b>$100</b> but vary depending on age and species of pet.</p>
                     <p>The adoption fee covers: spay/neuter surgery (legally required), current vaccines and boosters, a microchip with a lifetime registration, heartworm preventative until time of adoption, and a small bag of food. Please note: all dogs must leave with a collar and leash. You can bring these items with you or purchase them at the shelter.</p>
                     <div class="elementor-cta__button-wrapper elementor-cta__content-item elementor-content-item adopt-button" style="text-align: center;">
                        <a href="<?php echo $adoption_link; ?>"  target="_blank" class="elementor-button-link elementor-button elementor-size-lg" role="button">
                        <span class="elementor-button-content-wrapper">
                        <span class="elementor-button-text">Adopt <?php echo $pet->Name; ?></span>
                        </span>
                        </a>
                     </div>
                  </div>
                  <!--div class="elementor-cta__button-wrapper elementor-cta__content-item elementor-content-item " style="text-align: center;">
                     <a href="<?php echo DONATE_LINK; ?>" class="elementor-button-link elementor-button elementor-size-lg" role="button">
                         <span class="elementor-button-content-wrapper">
                     <span class="elementor-button-text">Sponsor Me</span>
                         </span>
                     </a>
                     </div-->
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
<?php
}
	public function display_no_animals_available($type) {

		?>
<div class="elementor-element elementor-element-3cf6776 elementor-widget elementor-widget-heading ghhs-center-align" data-id="3cf6776" data-element_type="widget" data-widget_type="heading.default">
   <div class="elementor-widget-container">
      <h2 class="elementor-heading-title elementor-size-default">
         No
         <?php
if ($type === 'Others') {
			echo "other animal types";
		} else {
			echo $type;
		}?>
         are available to adopt at this time.
      </h2>
      <h3>Please view another type of animal.</h3>
   </div>
</div>
<?php
}
}