<?php
/**
 * Add a Custom Post Type: Animal
 */
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
			// Register the Animal post type
			register_post_type(self::SLUG,
				array(
					'labels' => array(
						'name' => __(sprintf('%ss', ucwords(str_replace("_", " ", self::SLUG))), 'custom'),
						'singular_name' => __(ucwords(str_replace("_", " ", self::SLUG)), 'custom'),
					),
					'description' => __("Animal post type", 'custom'),
					'supports' => array(
						'title',
					),
					'public' => true,
					'show_ui' => true,
					'has_archive' => true,
					'show_in_menu' => GHHS_Animals_Settings::SLUG,
				)
			);

			if (function_exists("register_field_group")) {

				register_field_group(array(
					'key' => 'group_6069369c504f6',
					'title' => 'Animal',
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
							'label' => 'Name',
							'name' => 'name',
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
							'prepend' => '',
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
				));

			} // END if(function_exists("register_field_group"))
		} // END public function init()

	} // END class GHHS_Animals_PostType
} // END if(!class_exists('GHHS_Animals_PostType'))