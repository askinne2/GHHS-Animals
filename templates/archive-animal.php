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

<?php if (have_posts()): ?>

	<header class="page-header alignwide">
		<?php the_archive_title('<h1 class="page-title">', '</h1>');?>
		<?php if ($description): ?>
			<div class="archive-description"><?php echo wp_kses_post(wpautop($description)); ?></div>
		<?php endif;?>
	</header><!-- .page-header -->

	<?php while (have_posts()): ?>
		<?php the_post();?>
		<?php get_template_part('template-parts/content/content', get_theme_mod('display_excerpt_or_full_post', 'excerpt'));?>
	<?php endwhile;?>

	<?php twenty_twenty_one_the_posts_navigation();?>

<?php else: ?>
	<?php get_template_part('template-parts/content/content-none');?>
<?php endif;?>

<?php get_footer();?>


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


