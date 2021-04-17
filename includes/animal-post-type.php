<?php
/**
 * Add a Custom Post Type: Animal
 */
//require_once plugins_url(plugin_basename(__DIR__)) . 'ghhs_animals.php';

if (!class_exists('GHHS_Animals_PostType')) {
	class GHHS_Animals_PostType {
		const SLUG = "animal";

		/**
		 * Construct the custom post type for Reports
		 */
		public function __construct() {
			// register actions
			add_action('init', array(&$this, 'init'));

		} // END public function __construct()

		/**
		 * Hook into the init action
		 */
		public function init() {

			register_taxonomy(
				'adopt-animals', // The name of the taxonomy.
				'animal', // post type name
				array(
					'hierarchical' => false,
					'public' => true,
					'show_ui' => true,
					'label' => 'Animal Type', // display name
					'query_var' => true,
					'rewrite' => array('slug' => 'adopt'),

				)
			);

			// create taxonomy terms
			$terms = array(
				'0' => array('name' => 'Dog', 'slug' => 'dog'),
				'1' => array('name' => 'Cat', 'slug' => 'cat'),
				'2' => array('name' => 'Other', 'slug' => 'other'),
				'3' => array('name' => 'All Animal', 'slug' => 'all-animal'),

			);
			foreach ($terms as $term) {
				if (!term_exists($term['name'], 'adopt-animals')) {
					wp_insert_term($term['name'], 'adopt-animals', array('slug' => $term['slug']));
				}
				unset($term);
			}

			// Register the Animal post type
			register_post_type(self::SLUG,
				array(
					'labels' => array(
						'name' => __(sprintf('%ss', ucwords(str_replace("_", " ", self::SLUG))), 'custom'),
						'singular_name' => __(ucwords(str_replace("_", " ", self::SLUG)), 'custom'),
					),
					'description' => __("Animal post type", 'custom'),
					'supports' => array('title', 'excerpt', 'thumbnail', 'post-formats', 'taxonomy', 'custom-fields'),
					'taxonomies' => array('post-type', 'adopt-animals'),

					'public' => true,
					'show_ui' => true,
					'has_archive' => 'adopt',
					'show_in_menu' => GHHS_Animals_Settings::SLUG,
					'rewrite' => 'true',
				)
			);

			add_action('pre_get_posts', 'animals_pre_get_post');
			function animals_pre_get_post($query) {

				if (is_post_type_archive('animal') && !is_admin() && $query->is_main_query()) {
					$query->set('posts_per_page', -1);
					$query->set('orderby', 'menu_order');
				}
			}

			//if (function_exists("register_field_group")) {
			if (function_exists('acf_add_local_field_group')) {

				acf_add_local_field_group(array(
					'key' => 'group_6069369c504f6',
					'title' => 'Animal Info',
					'fields' => array(
						array(
							'key' => 'field_606937818cb9f',
							'label' => 'Animal ID',
							'name' => 'animal_id',
							'type' => 'text',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_606936d53d6e9',
							'label' => 'Animal Name',
							'name' => 'animal_name',
							'type' => 'text',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_606d1778e89f5',
							'label' => 'Cover Photo',
							'name' => 'cover_photo',
							'type' => 'image',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'return_format' => 'url',
							'preview_size' => 'medium',
							'library' => 'all',
							'min_width' => '',
							'min_height' => '',
							'min_size' => '',
							'max_width' => '',
							'max_height' => '',
							'max_size' => '',
							'mime_types' => '',
						),
						array(
							'key' => 'field_606937088cb9c',
							'label' => 'Color',
							'name' => 'color',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_606937418cb9d',
							'label' => 'Breed',
							'name' => 'breed',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_606937568cb9e',
							'label' => 'Status',
							'name' => 'status',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_606937868cba0',
							'label' => 'Age',
							'name' => 'age',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'min' => '',
							'max' => '',
							'step' => '',
						),
						array(
							'key' => 'field_606d1893e89f6',
							'label' => 'Photos',
							'name' => 'photos',
							'type' => 'gallery',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'return_format' => 'array',
							'preview_size' => 'medium',
							'insert' => 'append',
							'library' => 'all',
							'min' => '',
							'max' => '',
							'min_width' => '',
							'min_height' => '',
							'min_size' => '',
							'max_width' => '',
							'max_height' => '',
							'max_size' => '',
							'mime_types' => '',
						),
						array(
							'key' => 'field_606d18aee89f7',
							'label' => 'Videos',
							'name' => 'videos',
							'type' => 'oembed',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'width' => '',
							'height' => '',
						),
						array(
							'key' => 'field_606d18fae89f8',
							'label' => 'Bio',
							'name' => 'bio',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => 'Sorry! I\'m currently putting paw and pen together writing my autobiography!',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_606d1943e89f9',
							'label' => 'Adopt Link',
							'name' => 'adopt_link',
							'type' => 'url',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => 'https://www.shelterluv.com/matchme/adopt/ghhs-a-',
							'placeholder' => 'https://www.shelterluv.com/matchme/adopt/ghhs-a-',
						),
						array(
							'key' => 'field_606fd856c4074',
							'label' => 'Animal Type',
							'name' => 'animal_type',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_606fd862c4075',
							'label' => 'Sex',
							'name' => 'sex',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_6078559547367',
							'label' => 'Last Update Time',
							'name' => 'last_update_time',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_6078ecbe823c4',
							'label' => 'Animal Size',
							'name' => 'animal_size',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_607b06a05ae43',
							'label' => 'Adoption Fee',
							'name' => 'adoption_fee',
							'type' => 'number',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '$',
							'append' => '',
							'min' => '',
							'max' => '',
							'step' => '',
						),
					),
					'location' => array(
						array(
							array(
								'param' => 'post_type',
								'operator' => '==',
								'value' => self::SLUG,
							),
						),
					),
					'menu_order' => 0,
					'position' => 'normal',
					'style' => 'default',
					'label_placement' => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen' => '',
					'active' => true,
					'description' => '',
				));

			} // END if(function_exists("register_field_group"))
		} // END public function init()

	} // END class GHHS_Animals_PostType
} // END if(!class_exists('GHHS_Animals_PostType'))