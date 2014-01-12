<?php

	/**
 	* Plugin Name: Custom Fields In RSS
 	* Plugin URI: http://www.webentwickler.de/wordpress/custom_fields_in_rss
 	* Description: Extends RSS by custom fields from an arbitrary post.
 	* Version: 1.0
 	* Author: Frank Seidel
 	* Author URI: http://www.webentwickler.de
 	* License: GPL2
 	*/
 
 	class Extend_RSS_By_Custom_Fields {
 		function __construct() {
			add_action('rss2_item', 'Extend_RSS_By_Custom_Fields::add_my_custom_field_node');
 		}

		function add_my_custom_field_node() {
    	global $post;
    	$custom_fields = get_post_custom();
    
			foreach($custom_fields AS $key => $value) {
				if(substr($key, 0, 1) != "_") {
					foreach($value AS $_value) {
						$_value = trim($_value);
						if($_value) {
							echo("<$key>{$_value}</$key>\r\n");
						}
					}
				}
			}
 	 	}
 	}
 	
	$extended_rss = new Extend_RSS_By_Custom_Fields;

