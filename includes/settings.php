<?php

if (!class_exists('GHHS_Animals_Settings')) {
	class GHHS_Animals_Settings {

		const SLUG = "ghhs-animals-options";
		const UPDATESLUG = "ghhs-animals-update";

		/**
		 * Construct the plugin object
		 */
		public function __construct($plugin) {
			// register actions
			//$this->add_my_options_page();
			acf_add_options_page(array(
				'page_title' => __('GHHS Animals', 'custom'),
				'menu_title' => __('GHHS Animals', 'custom'),
				'menu_slug' => self::SLUG,
				'capability' => 'manage_options',
				'redirect' => false,
			));
			add_action('init', array(&$this, "init"));
			add_action('admin_menu', array(&$this, 'admin_menu'), 20);
			add_action('admin_menu', array(&$this, 'update_menu'), 20);
			add_filter("plugin_action_links_$plugin", array(&$this, 'plugin_settings_link'));
			add_filter("plugin_action_links_$plugin", array(&$this, 'update_settings_link'));
		} // END public function __construct

		/**
		 * Add options page
		 */
		public function admin_menu() {
			// Duplicate link into properties mgmt
			add_submenu_page(
				self::SLUG,
				__('Settings', 'custom'),
				__('Settings', 'custom'),
				'manage_options',
				self::SLUG,
				1
			);
		}
		public function update_menu() {
			// Duplicate link into properties mgmt
			add_submenu_page(
				self::UPDATESLUG,
				__('Options', 'custom'),
				__('Options', 'custom'),
				'manage_options',
				self::UPDATESLUG,
				1
			);
		}

		/**
		 * Add settings fields via ACF
		 */
		public function init() {

			if (function_exists('acf_add_local_field_group')):

				acf_add_local_field_group(array(
					'key' => 'group_6069369c504fd',
					'title' => 'Animal Info Settings',
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
								'param' => 'options_page',
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
					'description' => 'These are the field available to use in this custom plugin.',
				));

			endif;

		}

		/**
		 * Add the settings link to the plugins page
		 */
		public function plugin_settings_link($links) {
			$settings_link = sprintf('<a href="admin.php?page=%s">Settings</a>', self::SLUG);
			array_unshift($links, $settings_link);
			return $links;
		} // END public function plugin_settings_link($links)
		public function update_settings_link($links) {
			$update_link = sprintf('<a href="admin.php?page=%s">Update</a>', self::UPDATESLUG);
			array_unshift($links, $update_link);
			return $links;
		} // END public function plugin_settings_link($links)
	} // END class GHHS_Animals_Settings
} // END if(!class_exists('GHHS_Animals_Settings'))