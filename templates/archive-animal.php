<?php
/**
Archive Name: Animal Post
Archive Post Type: animal
 *
 * The archive template for displaying animals (custom post type)
 *
 *
 */

get_header();

$description = get_the_archive_description();
?>
<main id="site-content" role="main">
	<div class="container">
<?php if (have_posts()): ?>


		<?php the_archive_title('<h1 class="page-title">', '</h1>');?>
		<?php if ($description): ?>
			<div class="archive-description"><?php echo wp_kses_post(wpautop($description)); ?></div>
		<?php endif;?>
 <div class="entry-content">
        <?php the_title();?>
        <?php the_content();?>
    </div>

	<?php while (have_posts()): ?>
		<?php the_post();?>
					<h2> <?php echo the_field('Name'); ?> </h2>

		<!--?php get_template_part(plugin_dir_path(__FILE__) . 'template/animal');?-->
	<?php endwhile;?>


<!--?php else: ?
	php get_template_part(plugin_dir_path(__FILE__) . 'template/animal');?-->
<?php endif;?>
</div>
</main><!-- #site-content -->
<?php get_footer();?>


?>

