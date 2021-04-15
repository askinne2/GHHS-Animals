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

while (have_posts()): the_post();
	?>




						<main <?php post_class('site-main');?> role="main">
							<?php if (apply_filters('hello_elementor_page_title', true)): ?>
								<!-- Post Naviation -->
								<div class="container">
									<div class="row">
										<div class="col">
											<span><?php echo get_post_parent(); ?></span>
										</div>
									</div>
								</div>
								<header class="page-header">
									<?php the_title('<h1 class="entry-title single-animal-name fw-bold">', '</h1>');?>
								</header>
							<?php endif;?>
		<div class="page-content">
			<!--?php the_content();?-->

			<!-- FIX THIS ANDREW --->
			<div class="post-tags">
				<?php the_tags('<span class="tag-links">' . __('Tagged ', 'hello-elementor'), null, '</span><br>');?>
			</div>

			<!-- container for pet info -->
			<div class="container">
				<div class="row">
					<div class="col-6">
						<?php
printf('<a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal%s"><img src="%s" class="single-animal-cover-photo img-fluid" /></a>', get_the_id(), get_field('cover_photo'));
?>
					</div>
					<!-- start PET DETAILS div -->
					<div class="col-6 single-animal-details d-flex align-items-center">
						<div class="container">
							<div class="row">
								<?php printf("<h3>%s %s %s</h3>", get_field('color'), get_field('sex'), get_field('animal_type'));?>
							</div>
							<div class="row">
								<?php printf("<h3>Breed: %s </h3>", get_field('breed'));?>
							</div>
							<div class="row">
								<?php printf("<h3>Age: %s </h3>", get_field('age'));?>
							</div>


							<!-- action buttons -->

							<div class="row my-3">
								<?php printf('<a style="background-color: #0F9EDA;" class="text-white fw-bold btn btn-large" href="%s">Adopt %s</a>', get_field('adopt_link'), get_field('name'));?>
							</div>
							<div class="row my-3">
								<?php printf('<a style="background-color: #286B87;" class="text-white fw-bold btn btn-large" href="%s">Sponsor %s</a>', '/donate', get_field('name'));?>
							</div>
							<div class="row my-3">
								<?php printf('<button type="button" style="background-color: #286B87;" class="text-white fw-bold btn btn-large" data-bs-toggle="modal" data-bs-target="#exampleModal%s">More Photos</button>', get_the_id());?>
							</div>
						</div>
					</div> <!-- end pet-details div -->
				</div>
			</div> <!-- end container pet info -->

			<!-- PET PHOTOS -->
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
if ($photos):
	foreach ($photos as $photo):
	?>
																	<div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
																		<img class="img-fluid" src="<?php echo $photo; ?>" alt="<?php echo $photo ?>" />
																	</div>
																	<?php
endforeach;
else:
?>
											<div class ="col-lg-4 col-md-12 mb-4 mb-lg-0">
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

		</div> <!-- end PET PHOTOS -->
		<!-- PET bio -->
		<div class="container">
			<div class="row">
				<h3 class="my-5"><?php echo get_field('animal_name') ?>'s Biography</h3>
			</div>
			<div class="row">
				<?php printf('<p>%s</p>', get_field('bio'));?>
			</div>
		</div> <!-- end PET BIO -->

	</div> <!-- end page-content -->

	<?php comments_template();?>
</main>

<?php
endwhile;

get_footer();
