<?php
/*
Plugin Name: Simple Options
Plugin URI: http://siteorigin.com/wordpress-simple-options
Description: A very simple options library.
Author: Greg Priday
Version: 0.1.2
Author URI: http://siteorigin.com/
License: GPL 2.0
*/

define('SIMPLE_OPTIONS_VERSION', '0.1.2');

if(!function_exists('simple_options_get')) include(dirname(__FILE__).'/simple-options.functions.php');
include('simple-options.class.php');

/**
 * Just initialize all the options.
 */
function _simple_options_init(){
	do_action('simple_options_init');
}
add_action('init', '_simple_options_init');


/**
 * Add the admin menu page
 * 
 * @action admin_menu
 */
function _simple_options_add_theme_page(){
	if(!current_user_can('edit_theme_options')) return;
	$fields = simple_options_get_fields();
	if(empty($fields)) return;
	
	add_theme_page(
		__('Theme Options', 'origin'),
		__('Theme Options', 'origin'),
		'edit_theme_options',
		'simple-options',
		'_simple_options_render'
	);
}
add_action('admin_menu', '_simple_options_add_theme_page');

/**
 * Render the options page. Called by _simple_options_add_theme_page
 */
function _simple_options_render(){
	$fields = simple_options_get_fields();
	$values = simple_options_load();
	include(dirname(__FILE__).'/tpl/options.phtml');
}

/**
 * Enqueue the admin scripts and styles required by the options framework
 * @param $hook_suffix
 * 
 * @action admin_enqueue_scripts
 */
function _simple_options_enqueue_scripts($hook_suffix){
	if($hook_suffix != 'appearance_page_simple-options') return;

	wp_enqueue_script('simple-options', plugins_url('/js/options.js', __FILE__), array('jquery'), SIMPLE_OPTIONS_VERSION);
	wp_enqueue_style('simple-options', plugins_url('/css/options.css', __FILE__), array(), SIMPLE_OPTIONS_VERSION);
	wp_enqueue_script('jquery-ui-sortable');
	
	// A lot of the options use chosen
	wp_enqueue_script('chosen', plugins_url('/chosen/chosen.jquery.min.js', __FILE__), array('jquery'), '0.9.8');
	wp_enqueue_style('chosen', plugins_url('/chosen/chosen.css', __FILE__), array(), '0.9.8');
}
add_action('admin_enqueue_scripts', '_simple_options_enqueue_scripts');

/**
 * Save the options as they come in from $_POST
 */
function _simple_options_save(){
	if(!isset($_POST['_simpleoptions_nonce'])) return;
	if(!wp_verify_nonce($_POST['_simpleoptions_nonce'], 'save')) wp_die('Invalid Request');
	if(!current_user_can('edit_theme_options')) wp_die('Access Violation');

	$_POST = array_map('stripslashes_deep', $_POST);
	
	$option_values = simple_options_load();
	$option_pages = simple_options_get_pages();
	$option_fields = simple_options_get_fields();
	
	try{
		foreach($option_fields as $page_name => $page){
			foreach($page as $field_name => $field) {
				// The current value from the database
				$value = isset($option_values[$page_name][$field_name]) ? $option_values[$page_name][$field_name] : null;
				
				$field_class = 'SimpleOptionsField_'.ucfirst($field['type']);
				if(!class_exists($field_class)) continue;
				
				if(!isset($_POST['options'][$page_name])) $_POST['options'][$page_name] = array();
				if(!isset($_POST['options'][$page_name][$field_name])) $_POST['options'][$page_name][$field_name] = null;
				
				call_user_func(array($field_class, 'handle'), $field, $page_name, $field_name, $_POST['options'][$page_name][$field_name], $value);
				try{
					$value = call_user_func(array($field_class, 'validate'), $field, $_POST['options'][$page_name][$field_name]);
				}
				catch(Exception $e) {
					throw new SimpleOptionsField_Exception($page_name, $field_name, $e->getMessage());
				}
				
				$option_values[$page_name][$field_name] = $value;
				
			}
		}

		// We've made it this far, save the results to the database
		simple_options_store($option_values);
	}
	catch(SimpleOptionsField_Exception $error){
		simple_options_set_error($error);
	}
}
add_action('load-appearance_page_simple-options', '_simple_options_save');