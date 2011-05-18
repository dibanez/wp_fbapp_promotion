<?php
   /*
      Plugin Name: wp_fbapp_promotion
      Plugin URI: https://github.com/dibanez/wp_fbapp_promotion
      Description: plugin to manage promotions facebook 
      Tags: facebook app, wordpress facebook app, admin, custom, face book, Facebook, Facebook Widget, fb, fb app, featured, featured posts, Like, page, plugin, Post, posts, wordpress app, facebook recommend, wordpress facebook recommend, facebook send button, facebook send
      Version: 0.1
      Author: David Ibáñez Cerdeira (dibanez@gmail.com)
      Author URI: https://github.com/dibanez/wp_fbapp_promotion
   */

  /**
   * Install plugin in wordpress.
   * 
   *
   */
   	function wp_fbapp_promotion_install(){
		//   
		global $wpdb; 
		$table_promotions = $wpdb->prefix . "fb_promotions";
		$table_participants = $wpdb->prefix . "fb_participants";
		$table_results = $wpdb->prefix . "fb_results";
		
		$sql = "CREATE TABLE $table_promotions (
			id INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			promotion VARCHAR( 255 ) NOT NULL ,
			description TEXT NULL ,
			conditions TEXT NULL ,
			date_start DATE NOT NULL ,
			date_end DATE NOT NULL ,
			banner VARCHAR( 255 ) NULL ,
			active INT( 3 ) NOT NULL Default '0'
			) ENGINE = MYISAM; ";
		$wpdb->query($sql);
		
		$sql = "CREATE TABLE $table_participants (
			id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			idpromotion INT( 11 ) NULL ,
			idfacebook INT( 22 ) NULL ,
			username VARCHAR( 255 ) NULL ,
			link VARCHAR( 255 ) NULL ,
			code INT( 11 ) NULL ,
			date DATE NULL
			) ENGINE = MYISAM; ";
		$wpdb->query($sql);
	}

  /**
   * Desinstall plugin in wordpress.
   * 
   *
   */
	function wp_fbapp_promotion_desinstall(){
		//   
		global $wpdb; 
		$table_promotions = $wpdb->prefix . "fb_promotions";
		$table_participants = $wpdb->prefix . "fb_participants";
		$table_results = $wpdb->prefix . "fb_results";
		$sql = "DROP TABLE $table_promotions; ";
		$wpdb->query($sql);
		$sql = "DROP TABLE $table_participants; ";
		$wpdb->query($sql);
		$sql = "DROP TABLE $table_results; ";
		$wpdb->query($sql);
	}   

add_action('activate_wp_fbapp_promotion/wp_fbapp_promotion.php','wp_fbapp_promotion_install');
add_action('deactivate_wp_fbapp_promotion/wp_fbapp_promotion.php', 'wp_fbapp_promotion_desinstall');
   
?>