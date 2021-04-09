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
<main class="site-main" role="main">

	<?php if (apply_filters('hello_elementor_page_title', true)): ?>
		<header class="page-header">
			<?php
printf('<h1 class="elementor-heading-title elementor-size-default">Adopt an Animal</h1>');
printf('<h3 class="elementor-heading-title elementor-size-default">View our animals up for adoption at this time</h3>');

//the_archive_description('<p class="archive-description">', '</p>');
?>
		</header>
	<?php endif;?>
	<div class="page-content container">
<div class="row row-cols-3 row-cols-md-3 g-3 my-5">
		<?php
while (have_posts()) {
	the_post();
	$post_link = get_permalink();
	$animal_type = get_field('animal_type');
	?>
					<div class="col card text-center archive animal archive-animal" style="width: 18rem;">

			<!--article class="post archive-animal"-->

					<?php printf('<a href="%s"><img src="%s" class="card-img-top img-fluid" alt="%s"> </a>', esc_url($post_link), get_field('cover_photo'), esc_url($post_link));?>
  <div class="card-body my-3">
    <?php printf('<h3 class="card-title">%s</h3>', get_the_title());?>
    <p class="card-text"><?php printf("%s %s %s", get_field('color'), get_field('sex'), get_field('animal_type'));?></p>
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
		<?php } // while posts loop ?>
	</div> <!-- end card group -->
	</div> <!-- end container -->

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
