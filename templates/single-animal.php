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
								<?php the_title('<h1 class="entry-title single-animal-name">', '</h1>');?>
							</header>
						<?php endif;?>
		<div class="page-content">
			<?php the_content();?>

			<div class="post-tags">
				<?php the_tags('<span class="tag-links">' . __('Tagged ', 'hello-elementor'), null, '</span>');?>
			</div>

			<?php
$groups = acf_get_field_groups($post_id);
printf('<H2>GROUPS</H2><pre>%s</pre><br>', var_dump($groups));

$fields = acf_get_fields($groups[0]);
var_dump($fields);
foreach ($fields as $field) {
	printf("<p>field: %s</p>", get_field($field->id));

}

printf('<img src="%s" class"animal-pic" />', get_field('cover_photo'));
the_excerpt();
?>

		</div> <!-- end page-content -->

		<?php comments_template();?>
	</main>

	<?php
endwhile;

get_footer();
