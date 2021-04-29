	<?php
/**
Archive Name: Animal Post
Archive Post Type: animal
 *
 * The archive template for displaying animals (custom post type)
 *
 *
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

get_header();

?>
	<main role="main">



		<?php if (apply_filters('hello_elementor_page_title', true)): ?>
			<header class="page-header container">
				<?php
printf('<h1 class="elementor-heading-title elementor-size-default my-5 fs-1">Adopt an Animal</h1>');
printf('<h2 class="elementor-heading-title elementor-size-default">Give a fur-ever home to an animal in need.</h2>');

//the_archive_description('<p class="archive-description">', '</p>');
?>
			</header>

		<?php endif;?>
		<div class="page-content container">
			<div class="row my-5">
				<div class="col">
					<p>Please view our adoptable pets below.</p>
					<p>To begin your adoption process, please click the image (or button) of the pet you’d like to adopt to view more info about that animal. You will be redirected to Shelterluv to complete your adoption.</p>
				</div>
			</div>
			<div class="row my-5">
				<div class="col">
					<h3 class="elementor-heading-title elementor-size-default fw-bold my-3">Adoption Fees:</h3>
					<h4 class="elementor-heading-title elementor-size-default fw-bold my-3">Our adoption fees start at $100 but vary depending on age and species of pet.</h4>
				</div>
			</div>
			<div class="row my-3">
			<a id="archive-top">&nbsp;
			</a>
		</div>
			<?php if (PLUGIN_DEBUG) {printf('<h2 class="red_pet">Total Animals: %d.</h2>', $GLOBALS['wp_query']->post_count);}?>
			<div class="row post-tags container my-3">
							<?php
$terms = get_terms('adopt-animals');
$count = count($terms);
if ($count > 0) {
	echo '<ul class="list-group list-group-horizontal-sm">';
	foreach ($terms as $term) {?>

					<a href="<?php echo get_term_link($term->term_id); ?>" class="list-group-item list-group-item-action"><?php echo $term->name . 's'; ?> </a>

					 <?php }
	echo '</ul>';
}?>
								</div>
			<div class="row row-cols-4 row-cols-md-4 g-3 my-5">
				<?php
if (!have_posts()) {
	printf('<h2 class="red_pet">No adoptable animals at this time.</h2>');
} else {

	while (have_posts()) {
		the_post();
		$post_link = get_permalink();
		$animal_type = get_field('animal_type');
		?>
					<div class="col card text-center archive animal archive-animal mx-auto" style="width: 18rem;">

						<!--article class="post archive-animal"-->
						<?php printf('<a href="%s"><img src="%s" class="card-img-top img-fluid" alt="%s"> </a>', esc_url($post_link), get_the_post_thumbnail_url($post, 'large'), esc_url($post_link));?>
						<div class="card-body my-3">
							<?php printf('<h4 class="card-title">%s</h4>', get_the_title());?>
							<p class="card-text"><?php printf("%s %s %s", get_field('color'), get_field('sex'), get_field('animal_type'));?></p>

							<?php printf("<p>Breed: %s </p>", get_field('breed'));?>


							<?php printf("<p>Age: %s </p>", get_field('age'));?>

							<!--?php if (get_field('animal_size')) {printf("<p>Size: %s </p>", get_field('animal_size'));} else {echo '<p>Size Info Unavailable</p>';}?-->

							<?php printf('<a href="%s" style="background-color: #0F9EDA;"  class="text-white btn btn-large">More Info</a>', esc_url($post_link));?>
						</div>

						<?php
/*$groups = acf_get_field_groups($post_id);
		printf('<H2>GROUPS</H2><pre>%s</pre><br>', var_dump($groups));

		$fields = acf_get_fields($groups[0]);
		var_dump($fields);
		foreach ($fields as $field) {
		printf("<p>field: %s</p>", get_field($field->id));

		}
		 */
		?>
		<!--/article-->
	</div> <!-- end card div -->
	<?php
} // while posts loop
} // end if (have_posts())
?>
	</div> <!-- end card group -->

	</div> <!-- end container -->


	<?php printf('<a href="#archive-top" style="background-color: #0F9EDA;"  class="text-white btn btn-large">Back to Top</a>');?>

	<!-- POST TAGS --->
					<div class="post-tags container mt-5 my-5">
						<?php
$terms = get_terms('adopt-animals');
$count = count($terms);
if ($count > 0) {
	echo '<ul class="list-group list-group-horizontal-sm">';
	foreach ($terms as $term) {?>

								<a href="<?php echo get_term_link($term->term_id); ?>" class="list-group-item list-group-item-action"><?php echo $term->name . 's'; ?> </a>

							<?php }
	echo '</ul>';
}?>
					</div>
	<?php wp_link_pages();?>

		<?php
global $wp_query;
if ($wp_query->max_num_pages > 1):
?>
			<nav class="pagination" role="navigation">
				<?php /* Translators: HTML arrow */?>
				<div class="nav-previous"><?php next_posts_link(sprintf(__('%s older', 'hello-elementor'), '<span class="meta-nav">&larr;</span>'));?></div>
				<?php /* Translators: HTML arrow */?>
				<div class="nav-next"><?php previous_posts_link(sprintf(__('newer %s', 'hello-elementor'), '<span class="meta-nav">&rarr;</span>'));?></div>
			</nav>
		<?php endif;?>
	</main>

	<?php
get_footer();
