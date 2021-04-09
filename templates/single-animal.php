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
																		<header class="page-header">
																			<?php the_title('<h1 class="entry-title single-animal-name fw-bold">', '</h1>');?>
																		</header>
																	<?php endif;?>
		<div class="page-content">
			<?php the_content();?>

			<!-- FIX THIS ANDREW --->
			<div class="post-tags">
				<?php the_tags('<span class="tag-links">' . __('Tagged ', 'hello-elementor'), null, '</span><br>');?>
			</div>

			<!-- container for pet info -->
			<div class="container">
				<div class="row">
					<div class="col-6">
						<?php
printf('<img src="%s" class="single-animal-cover-photo img-fluid" />', get_field('cover_photo'));
?>
					</div>
					<!-- start PET DETAILS div -->
					<div class="col-6 single-animal-details d-flex align-items-center">
						<div class="container">
							<div class="row">
								<?php printf("<h3>%s %s %s</h3>", get_field('color'), get_field('sex'), get_field('type'));?>
							</div>
							<div class="row">
								<?php printf("<h3>Breed: %s </h3>", get_field('breed'));?>
							</div>
							<div class="row">
								<?php printf("<h3>Age: %d </h3>", get_field('age'));?>
							</div>


							<!-- action buttons -->

							<div class="row my-3">
								<?php printf('<a style="background-color: #0F9EDA;" class="text-white btn btn-primary btn-large" href="%s">Adopt %s</a>', get_field('adopt_link'), get_field('name'));?>
							</div>
							<div class="row my-3">
								<?php printf('<a style="background-color: #286B87;" class="text-white btn btn-primary btn-large" href="%s">Sponsor %s</a>', '/donate', get_field('name'));?>
							</div>
						</div>
					</div> <!-- end pet-details div -->
				</div>
			</div> <!-- end container pet info -->

			<!-- PET PHOTOS -->
			<div class="container">
				<!-- Slider main container -->
				<div class="row">
					<h3 class="text-center my-5 fw-bold">More Photos</h3>
				</div>
				<div class="row">

					<?php
$photos = get_field('photos');
$size = 'full';
if ($photos):
	foreach ($photos as $photo):
	?>
																						<div class="col">
																							<img class="img-fluid" src="<?php echo esc_url($photo['url']); ?>" alt="<?php echo esc_attr($photo['alt']); ?>" />
																						</div>
																						<?php
endforeach;
endif;
?>

				</div>
			</div> <!-- end PET PHOTOS -->
<!-- PET bio -->
			<div class="container">
				<div class="row">
					<h3 class="text-center my-5"><?php echo get_field('name') ?>'s Biography</h3>
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
