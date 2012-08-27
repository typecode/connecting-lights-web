<?php

/**
 * Add an options field
 *
 * @param string $page
 * @param array $settings
 */
function simple_options_add_page($page, $settings){
	global $simple_options_pages;
	$simple_options_pages[$page] = $settings;
}

/**
 * Add an options field
 *
 * @param string $page
 * @param string $field
 * @param array $settings
 */
function simple_options_add($page, $field, $type, $settings){
	global $simple_options_fields;
	$settings['type'] = $type;
	if(empty($simple_options_fields[$page])) $simple_options_fields[$page] = array();
	$simple_options_fields[$page][$field] = $settings;
}

/**
 * Add a separator
 *
 * @param $page
 * @param $field
 * @param $text
 */
function simple_options_add_section_title($page, $field, $text){
	global $simple_options_sections;
	if(empty($simple_options_sections)) $simple_options_sections = array();
	$simple_options_sections[$page.':'.$field] = $text;
}

/**
 * Get a separator
 *
 * @param $page
 * @param $field
 * @return null
 */
function simple_options_get_section_title($page, $field = null){
	global $simple_options_sections;
	return isset($simple_options_sections[$page.':'.$field]) ? $simple_options_sections[$page.':'.$field] : null;
}

/**
 * Get an option value
 *
 * @param string $page
 * @param string $field
 * @return mixed|WP_Error
 */
function simple_options_get($page, $field){
	global $simple_options_fields;
	if(!isset($simple_options_fields[$page])) return new WP_Error('', 'Unknown options page');
	if(!isset($simple_options_fields[$page][$field])) return new WP_Error('', 'Unknown field');

	$values = simple_options_load();
	if(isset($values[$page][$field])){
		return $values[$page][$field];
	}
	else{
		if(!isset($simple_options_fields[$page][$field]['default'])) return false;
		else return $simple_options_fields[$page][$field]['default'];
	}
}

/**
 * Store the options in the database
 */
function simple_options_store($option_values){
	global $simple_options_values, $simple_options_updated;
	$simple_options_values = $option_values;
	$theme = basename(get_template_directory());

	update_option('simple-options-'.$theme, $simple_options_values);

	$simple_options_updated = true;
}

/**
 * Load the values from the database
 *
 * @return array All the current values
 */
function simple_options_load(){
	global $simple_options_values, $simple_options_fields;
	if(!is_null($simple_options_values)) return $simple_options_values;

	$simple_options_values = get_option('simple-options-'.basename(get_template_directory()), array());
	
	// Add in all the default values
	if(!empty($simple_options_fields)) {
		foreach((array) $simple_options_fields as $page_name => $fields){
			foreach($fields as $field_name => $field){
				if(!isset($simple_options_values[$page_name][$field_name]) && isset($simple_options_fields[$page_name][$field_name]['default'])){
					$simple_options_values[$page_name][$field_name] = $simple_options_fields[$page_name][$field_name]['default'];
				}
			}
		}
	}
	
	return $simple_options_values;
}

/**
 * @return array All the pages
 */
function simple_options_get_pages(){
	global $simple_options_pages;
	if(empty($simple_options_pages)) return array();
	return $simple_options_pages;
}

/**
 * @return array All the fields
 */
function simple_options_get_fields(){
	global $simple_options_fields;
	if(empty($simple_options_fields)) return array();
	return $simple_options_fields;
}

/**
 * Set an options error
 * @param SimpleOptionsField_Exception $error
 */
function simple_options_set_error($error){
	global $simple_options_error;
	$simple_options_error = $error;
}

function simple_options_get_error(){
	global $simple_options_error;
	if(empty($simple_options_error)) return false;
	else return $simple_options_error;
}

/**
 * Check if options were updated
 * @return bool
 */
function simple_options_is_updated(){
	global $simple_options_updated;
	return !empty($simple_options_updated);
}