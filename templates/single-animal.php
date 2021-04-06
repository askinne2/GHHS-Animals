<?php
/**
Template Name: Animal Post
Template Post Type: animal
 *
 * The template for displaying single animal posts (custom post type)
 *
 *
 */
get_header();

?>
<!--start container-->
<?php while (have_posts()): the_post();?>


									      <?php the_title();?>

									      <?php the_content();?>
									      <?php
	echo "<h2>";
	the_field('name');
	echo "</h2>"; ?>


									<?php endwhile;?>
<!--end container-->
<?php get_footer()?>


