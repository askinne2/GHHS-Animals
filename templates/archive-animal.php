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
	<div class="page-content">
		<?php
while (have_posts()) {
	the_post();
	$post_link = get_permalink();
	?>
			<article class="post archive-animal">
					<?php
$groups = acf_get_field_groups($post_id);
	printf('<H2>GROUPS</H2><pre>%s</pre><br>', var_dump($groups));

	$fields = acf_get_fields($groups[0]);
	var_dump($fields);
	foreach ($fields as $field) {
		printf("<p>field: %s</p>", get_field($field->id));

	}

	printf('<h2 class="%s"><a href="%s">%s</a></h2>', 'entry-title', esc_url($post_link), esc_html(get_the_title()));
	printf('<a href="%s">%s</a>', esc_url($post_link), get_the_post_thumbnail($post, 'medium'));
	the_excerpt();
	?>
			</article>
		<?php }?>
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
