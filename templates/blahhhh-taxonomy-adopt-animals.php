<?php
/*

displays all taxonomies for adoptable animals
 */
get_header();?>

<div id="main-content" class="main-content">

    <div id="primary" class="content-area">
        <div id="content" class="site-content" role="main">

        <?php //list terms in a given taxonomy using wp_list_categories (also useful as a widget if using a PHP Code plugin)

$taxonomy = 'adopt-animals';
$orderby = 'name';
$show_count = 0; // 1 for yes, 0 for no
$pad_counts = 0; // 1 for yes, 0 for no
$hierarchical = 0; // 1 for yes, 0 for no
$title = 'Blah';

$args = array(
	'taxonomy' => $taxonomy,
	'orderby' => $orderby,
	'show_count' => $show_count,
	'pad_counts' => $pad_counts,
	'hierarchical' => $hierarchical,
	'title_li' => $title,
);

?>

        <ul>
            <?php wp_list_categories($args);?>
        </ul>

        </div><!-- #content -->
    </div><!-- #primary -->
    <?php get_sidebar('content');?>
</div><!-- #main-content -->

<?php
get_footer();