<?php
/**
 *
 * This file registers a custom post type 'animals' for use in the plugin.
 *
 *
 */

require __DIR__ . '/vendor/autoload.php';
use PostTypes\PostType;
use PostTypes\Taxonomy;

class GHHS_Animals {

	public function __construct() {
// Create a book post type.
		$animals = new PostType('animal');

// Attach the genre taxonomy (which is created below).
		$animals->taxonomy('type');

// Hide the date and author columns.
		$animals->columns()->hide(['date', 'author']);

// Create a genre taxonomy.
		$types = new Taxonomy('type');

// Set options for the taxonomy.
		$types->options([
			'hierarchical' => false,
		]);

// Register the taxonomy to WordPress.
		$types->register();

// Set the animals menu icon.
		$animals->icon('dashicons-buddicons-activity');

// Register the post type to WordPress.
		//$animals->filters(['dogs', 'cats', 'others']);
		$animals->register();

	}

	public function register_fields() {

		if (function_exists('acf_add_local_field_group')):

			my_acf_add_local_field_group(array(
				'key' => 'group_6069369c504fd',
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
							'value' => 'animal',
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

		endif;
		add_action('acf/init', 'my_acf_add_local_field_groups');

	}

}
