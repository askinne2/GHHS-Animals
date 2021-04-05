<?php

/**
 * Template Name: Archive Animal
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

get_header();

?>

<div id="primary">
	<div id="content" role="main">

		<?php while (have_posts()): the_post();?>

								<h1><?php the_field('Name');?></h1>

								<p><?php the_content();?></p>

							<?php endwhile; // end of the loop. ?>

	</div><!-- #content -->
</div><!-- #primary -->

<?php get_footer();?>