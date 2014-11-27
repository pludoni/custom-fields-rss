<?php

  /**
  * Plugin Name: Empfehlungsbund Feederweiterung
  * Plugin URI: http://www.empfehlungsbund.de/validator
  * Description: Fügt Custom-Fields zu einem RSS-Feed hinzu.
  * Version: 1.0
  * Author: pludoni GmbH
  * Author URI: http://www.pludoni.de
  * License: GPL2
  */

  class Extend_RSS_By_Custom_Fields {
    function __construct() {
      add_action('rss2_item', 'Extend_RSS_By_Custom_Fields::add_my_custom_field_node');
      add_action('rss2_ns', 'Extend_RSS_By_Custom_Fields::add_ns');
    }
    function add_ns() {
      echo 'xmlns:eb="http://www.empfehlungsbund.de/schema.dtd"';
                }
    function add_my_custom_field_node() {
          global $post;
          $custom_fields = get_post_custom();

      foreach($custom_fields AS $key => $value) {
        if(substr($key, 0, 1) != "_") {
          foreach($value AS $_value) {
            $_value = htmlspecialchars(trim($_value));
            $key = strtolower($key);
            if($_value) {
              echo("<eb:$key>{$_value}</eb:$key>\r\n");
            }
          }
        }
      }
    }
  }

  $extended_rss = new Extend_RSS_By_Custom_Fields;
