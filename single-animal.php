<?php
/**
Template Name: Animal Post
Template Post Type: animal
 *
 * The template for displaying single animal posts (custom post type)
 *
 *
 */
get_header('');

?>
<!--start container-->
<?php while (have_posts()): the_post();?>
			<div class="container">
			  <div class="row">
			    <div class="col-12 col-sm-12 col-md-12">

			      <?php the_title();?>

			      <?php the_content();?>
			      <?php
	echo "<h2>";
	the_field('name');
	echo "</h2>"; ?>

			    </div>
			  </div>
			</div>
			<?php endwhile?>
<!--end container-->
<?php get_footer()?>


