<?php
/**
 * Author: droitthemes
 * @package faded
 * @subpackage faded
 * @since faded 1.0
 * */

add_action( 'init', 'register_screenshot_post_type' );
function register_screenshot_post_type() {
   register_post_type( 'screenshot',
		array(
			'labels' => array(
				'name'			=> __('ScreenShot','faded'),
				'singular_name' => __('ScreenShot','faded'),
				'add_new'		=> __('Add ScreenShot Item','faded'),
				'add_new_item'	=> __('Add ScreenShot Item','faded'),
				'edit_item'		=> __('Edit ScreenShot Item','faded'),
				'new_item'		=> __('New ScreenShot Item','faded'),
				'not_found'		=> __('No ScreenShot Item found','faded'),
				'not_found_in_trash' => __('No ScreenShot Item found in Trash','faded'),
				'menu_name'		=> __('ScreenShot','faded'),
			),
			'description' => 'Manage with our ScreenShot',
			'public' => true,
			'show_in_nav_menus' => true,
			'supports' => array(
				'title',
				'thumbnail',
				'editor',
			),
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 6,
			'has_archive' => true,
                        'menu_icon' => 'dashicons-editor-table',
			'query_var' => true,
			'rewrite' => array('slug' => 'screenshot'),
			'capability_type' => 'post',
			'map_meta_cap'=>true
		)
	);
	
	add_custom_taxonomies_portfolio();
	flush_rewrite_rules(false);
}
function add_custom_taxonomies_portfolio() {
	register_taxonomy('scr_cat', 'screenshot', array(
		'hierarchical' => true,
                'show_admin_column' => true,
		'labels' => array(
			'name' => _x( 'Src-category', 'taxonomy general name','faded' ),
			'singular_name' => _x( 'ScreenShot', 'taxonomy singular name' ,'faded'),
			'search_items' =>  __( 'Search Locations' ,'faded'),
			'all_items' => __( 'All ScreenShot' ,'faded'),
			'parent_item' => __( 'Parent Location' ,'faded'),
			'parent_item_colon' => __( 'Parent Location:' ,'faded'),
			'edit_item' => __( 'Edit Location' ,'faded'),
			'update_item' => __( 'Update ScreenShot' ,'faded'),
			'add_new_item' => __( 'Add New ScreenShot' ,'faded'),
			'new_item_name' => __( 'New ScreenShot' ,'faded'),
			'menu_name' => __( 'ScreenShot Catagory' ,'faded'),
		),
		'rewrite' => array(
			'slug' => 'scr_cat', 
			'with_front' => false, 
			'hierarchical' => true 
		)
		
	));
}