<?php

/**
Template Name: Animal Post
Template Post Type: animal
 *
 * The template for displaying single animal posts (custom post type)
 *
 *
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

get_header();

while (have_posts()) : the_post();
?>

	<main role="main">
		<!-- POST TAGS --->
		<div class="post-tags container my-5">
			<?php
			$terms = get_terms('adopt-animals');
			$count = count($terms);
			if ($count > 0) {
				echo '<ul class="list-group list-group-horizontal-sm">';
				foreach ($terms as $term) { ?>

					<a href="<?php echo get_term_link($term->term_id); ?>" class="list-group-item list-group-item-action"><?php echo $term->name . 's'; ?> </a>

			<?php }
				echo '</ul>';
			} ?>
		</div>
		<?php if (apply_filters('hello_elementor_page_title', true)) : ?>

			<header class="page-header">
				<?php the_title('<h1 class="entry-title single-animal-name fw-bold">', '</h1>'); ?>
			</header>
		<?php endif; ?>
		<div class="page-content container">




			<!-- container for pet info -->
			<div class="container">
				<div class="row">
					<div class="col-md-6">
						<?php
						//printf('<a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal%s"><img src="%s" class="single-animal-cover-photo img-fluid" /></a>', get_the_id(), get_field('cover_photo'));
						printf('<a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal%s">%s</a>', get_the_id(), get_the_post_thumbnail($post, 'large'));

						?>
					</div>
					<!-- start PET DETAILS div -->
					<div class="col-md-6 single-animal-details d-flex align-items-center">
						<div class="container">
							<div class="row">
								<?php printf("<h5>%s %s %s</h5>", get_field('color'), get_field('sex'), get_field('animal_type')); ?>
							</div>
							<div class="row">
								<?php printf("<h5>Breed: %s </h5>", get_field('breed')); ?>
							</div>
							<div class="row">
								<?php printf("<h5>Age: %s </h5>", get_field('age')); ?>
							</div>
							<div class="row">
								<?php if (get_field('animal_size')) {
									printf("<h5>Size: %s </h5>", get_field('animal_size'));
								} else {
									echo '';
								} ?>
							</div>
							<!-- PET bio -->
							<?php if (get_field('bio')) : ?>

								<div class="row">
									<h5 class="my-2"><?php echo get_field('animal_name') ?>'s Biography</h5>
								</div>
								<div class="row">
									<?php printf('<p>%s</p>', get_field('bio')); ?>
								</div>
							<?php endif; ?>

							<div class="row">
								<p>To begin your adoption process, please click the adopt button below. You will be redirected to Shelterluv to complete your adoption.</p>
							</div>
							<div class="row">
								<p>Adoption Fees vary based on pet.</p>
							</div>

							<!-- action buttons -->

							<div class="row my-3">
								<?php
								//printf('<a style="background-color: #0F9EDA;" class="text-white fw-bold btn btn-large" href="%s">Adopt %s</a>', get_field('adopt_link'), get_field('animal_name'));
								printf('<button type="button" style="background-color: #0F9EDA;" class="text-white fw-bold btn btn-large" data-bs-toggle="modal" data-bs-target="#adoptInfoModal">Adopt %s</button>', get_field('animal_name'));
								?>
							</div>
							<!--div class="row my-3">
								<?php //printf('<a style="background-color: #286B87;" class="text-white fw-bold btn btn-large" href="%s">Sponsor %s</a>', '/donate', get_field('name'));
								?>
							</div-->
							<div class="row my-3">
								<?php printf('<button type="button" style="background-color: #da9240;" class="text-white fw-bold btn btn-large" data-bs-toggle="modal" data-bs-target="#exampleModal%s">More Photos</button>', get_the_id()); ?>
							</div>
						</div>
					</div> <!-- end pet-details div -->
				</div>
			</div> <!-- end container pet info -->

			<!-- PET PHOTOS MODAL -->
			<div class="container">
				<!-- Slider main container -->
				<div class="row">
					<div class="col ">
						<?php
						// Modal
						printf('<div class=" modal fade" id="exampleModal%s" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">', get_the_id());
						?>
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">More Photos</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<div class="row">

										<?php
										$photos = get_post_meta(get_the_id(), 'photos');
										//print_r($photos);
										if ($photos) :
											foreach ($photos as $photo) :
										?>
												<div class="col-lg-4 col-md-12 my-1 my-lg-1">
													<img class="img-fluid" src="<?php echo $photo; ?>" alt="<?php echo $photo ?>" />
												</div>
											<?php
											endforeach;
										else :
											?>
											<div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
												<h3 class="text-center fw-bold">No other images to display</h3>
											</div>
										<?php
										endif;
										?>

									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
								</div>
							</div>
						</div> <!-- end modal-dialog -->
					</div>

				</div>
				<div class="col"></div>
				<div class="col"></div>
			</div><!-- end more photos button row -->

		</div> <!-- end PET PHOTOS MODAL -->

		<!-- ADOPT INFO MODAL -->
		<div class="container">
			<!-- Slider main container -->
			<div class="row">
				<div class="col ">
					<?php
					// Modal
					printf('<div class=" modal fade" id="adoptInfoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">', 'adoptinfo');
					?>
					<div class="modal-dialog">
						<div class="modal-content p-3">
							<div class="modal-header">
								<h5 class="modal-title" id="exampleModalLabel">Important Adoption Info</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body container">
								<div class="row">

									<h3>Our mission is to complete families through a thoughtful and thorough adoption process.</h3>
									<p> To ensure every animal is placed in a forever home, GHHS begins this process with a detailed adoption application. You will fill out the application after choosing an animal to adopt. To be considered for adoption:</p>
									<ul>
										<li>You must be at least 19 years old.</li>
										<li>If you rent housing, you must have written permission from your landlord and proof of pet deposit.</li>
										<li>If adopting into a family, we require all members of the family (including current dogs) to meet the animal on GHHS premises. This also means that animals cannot be adopted as "surprises" or "presents."</li>
										<li>Some dogs may require a home inspection.</li>
									</ul>
									<p><b>Meeting these guidelines is not a guarantee that your application will be accepted. GHHS reserves the right to adopt only to qualified homes based upon our guidelines. Each adoption is considered on a first-come, first-qualified basis once the animal is available for adoption. Exceptions may be made for potential adopters.</b></p>
									<?php
									if (get_field('adoption_fee') == 0) {
										printf('<p>Adoption Fees vary based on pet.</p>');
									} else {
										printf('<p>Adoption Fee for %s: $%0.2f</p>', get_field('animal_name'), get_field('adoption_fee'));
									}
									?>
									<p>The adoption fee covers: spay/neuter surgery (legally required), current vaccines and boosters, a microchip with a lifetime registration, heartworm preventative until time of adoption, and a small bag of food. Please note: all dogs must leave with a collar and leash. You can bring these items with you or purchase them at the shelter.</p>
								</div>
							</div>
							<div class="modal-footer">
								<?php printf('<a style="background-color: #0F9EDA;" class="text-white fw-bold btn" href="%s">Adopt %s</a>', get_field('adopt_link'), get_field('animal_name')); ?>
								<button type="button" class="btn" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div> <!-- end modal-dialog -->
				</div>

			</div>
			<div class="col"></div>
			<div class="col"></div>
		</div><!-- end adopt info button row -->

		</div> <!-- end ADOPT INFO MODAL -->


		<!-- POST TAGS --->
		<div class="post-tags container mt-5 my-5">
			<?php
			$terms = get_terms('adopt-animals');
			$count = count($terms);
			if ($count > 0) {
				echo '<ul class="list-group list-group-horizontal-sm">';
				foreach ($terms as $term) { ?>

					<a href="<?php echo get_term_link($term->term_id); ?>" class="list-group-item list-group-item-action"><?php echo $term->name . 's'; ?> </a>

			<?php }
				echo '</ul>';
			} ?>
		</div>
		</div> <!-- end page-content -->

	</main>

<?php
endwhile;

get_footer();
