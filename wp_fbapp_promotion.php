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

if( !class_exists( 'wp_fbapp_promotion' ) ) {
	class wp_fbapp_promotion {
		
		var $version = "0.1";
		var $options;
		var $is_installed = false;
		
		var $page_slugs;
		
		var $promotion_table;
		var $participants_table;
		var $results_table;
		
		/**
		 * Default 
		 * constructor initializes variables and other data needed for the plugin to operate
		 * correctly.
		 *
		 * @return wp_fbapp_promotion A newly constructed instance of the WP_PM object with all data initialized.
		 */
		function wp_fbapp_promotion() {
			global $wpdb;
			
			// Setup the table names
			$this->promotion_table = $wpdb->prefix . 'promotions';
			$this->participants_table = $wpdb->prefix . 'participants';
			$this->results_table = $wpdb->prefix . 'results';
			
			if( isset( $_POST[ 'uninstall_wp_fbapp_promotion' ] ) ) {
				$this->uninstall();
			}

			
			if( get_option( 'WP-FBapp-Promotion Options' ) !== FALSE ) {
				$this->options = unserialize( get_option( 'WP-FBapp-Promotion Options' ) ); 
			} else {
				$this->options = array();
			}
			
			if( get_option( 'WP-FBapp-Promotion Version' ) !== FALSE ) {
				$this->is_installed = true;
			}
			
			// Setup the page slug array
			$this->page_slugs = array();
			
		}
		
		/**
		 * Check to see if tables for the WP-FBapp-Promotion plugin are installed and that the plugin is the current version.
		 * If those two things are true, then leave the data alone.  Otherwise, upgrade or install the necessary
		 * tables.
		 */
		function on_activate() {
			
			$current_version = get_option( 'WP-FBapp-Promotion Version' );
			
			// Install the various tables if they don't exist already
			if( FALSE === $current_version ) {
				// The plugin isn't installed, so we'll install it
				$this->install();
				
			} else if( $this->version == $current_version ) {
				// The plugin is already updated and this is just a reactivation, so do nothing
				
			} else {
				// The plugin is being upgraded, let's do an upgrade
				$this->upgrade( $current_version );
				
			}
			
		}
		
		/**
		 * This function will not make any changes to data that exists in the database.  That is reserved for the 
		 * uninstall_data function.  For now, this is just a placeholder in case some action becomes necessary
		 * on deactivation.
		 */
		function on_deactivate() {
			// We're not really doing anything on a deactivation, because everything is being uninstalled
			// through a separate mechanism to ensure none of the good data gets erased.
			
		}
	
		/**
		 * Adds all additional pages necessary for the correct administration of WP-FBapp-Promotion, as well as enqueueing any
		 * JavaScript files necessary for those files.
		 */
		function on_admin_menu() {
			$this->page_slugs[ 'top_level' ] = add_menu_page( 'WP-FBapp-Promotion', 'WP-FBapp-Promotion', 8, 'wp_fbapp_promotion', array( &$this, 'top_level_page' ) );
			
			if( $this->is_installed ) {
				
				wp_enqueue_script( 'wp_fbapp_promotion', get_bloginfo( 'siteurl' ) . '/wp-content/plugins/wp_fbapp_promotion/js/wp_fbapp_promotion.js', array( 'jquery' ) );
				$this->page_slugs[ 'promotions' ]  = add_submenu_page( 'wp_fbapp_promotion', 'Promotions', 'Promotions', 8, 'wp_fbapp_promotion/promotions', array( &$this, 'promotions_page' ) );
				$this->page_slugs[ 'participants' ]  = add_submenu_page( 'wp_fbapp_promotion', 'Participants', 'Participants', 8, 'wp_fbapp_promotion/participants', array( &$this, 'participants_page' ) );
				$this->page_slugs[ 'raffle' ]  = add_submenu_page( 'wp_fbapp_promotion', 'Raffle', 'Raffle', 8, 'wp_fbapp_promotion/raffle', array( &$this, 'raffle_page' ) );
				// $this->page_slugs[ 'options' ]  = add_submenu_page( 'wp_fbapp_promotion', 'Options', 'Options', 8, 'wp_fbapp_promotion/options', array( &$this, 'option_page' ) );
				$this->page_slugs[ 'uninstall' ] = add_submenu_page( 'wp_fbapp_promotion', 'Uninstall', 'Uninstall', 8, 'wp_fbapp_promotion/uninstall', array( &$this, 'uninstall_page' ) );
			}
			
			$this->page_slugs[ 'about' ]  = add_submenu_page( 'wp_fbapp_promotion', 'About', 'About', 8, 'wp_fbapp_promotion/about', array( &$this, 'about_page' ) );
			$this->page_slugs[ 'donate' ]  = add_submenu_page( 'wp_fbapp_promotion', 'Donate', 'Donate', 8, 'wp_fbapp_promotion/donate', array( &$this, 'donate_page' ) );
		}
		
		/**
		 * Selectively prints information to the head section of the administrative HTML section.
		 */
		function on_admin_head() {
			if( strpos( $_SERVER['REQUEST_URI'], 'wp_fbapp_promotion' ) ) {
				?>
				<link rel="stylesheet" href="<?php bloginfo( 'siteurl' ); ?>/wp-admin/css/dashboard.css?version=2.5.1" type="text/css" />
				<link rel="stylesheet" href="<?php bloginfo( 'siteurl' ); ?>/wp-content/plugins/wp_fbapp_promotion/css/wp_fbapp_promotion.css" type="text/css" />
				<?php
				if( strpos( $_SERVER[ 'REQUEST_URI' ], 'tasks' ) ) {
				?>
				<script type="text/javascript">
					var old_id = <?php echo $this->options[ 'current_timer' ]; ?>
				</script>
				<?php
				}
			}
		}
	
		/**
		 * Perform initialization required after WordPress loads but before any HTTP headers
		 * are sent.
		 */
		function on_init() {
			
		}
		
		
		/**
		 * Toggles the currently active timer, and saves whatever time was 
		 *
		 */
		function on_timer_toggle() {
			$current_timer = $this->options[ 'current_timer' ];
			$current_timer_started = $this->options[ 'timer_started' ];
			
			$new_timer = $_POST[ 'timer_id' ];
			$new_timer_started = time();
			
			if( $current_timer == $new_timer ) {
				$this->options[ 'current_timer' ] = -1;
				$this->options[ 'timer_started' ] = 0;				
				
			} else {
				$this->options[ 'current_timer' ] = $new_timer;
				$this->options[ 'timer_started' ] = $new_timer_started;
				
			}
			
			
			update_option( 'WP-FBapp-Promotion Options', serialize( $this->options ) );
			$this->add_seconds_to_task( $current_timer, $new_timer_started - $current_timer_started );
			
			echo $new_timer;
			
			exit;
		}
	
	
		/**
		 * The following functions are all utility functions for the plugin.
		 */
		
		/**
		 * Installs the plugin for the first time by creating all tables and storing all the options
		 * that need to be stored.
		 */
		function install() {
			
			global $wpdb;
			
			
			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			
			// Make sure the promotions table doesn't already exist
			if( $wpdb->get_var( "SHOW TABLES LIKE '$this->promotion_table'" ) != $this->promotion_table ) {
				
				// Create the table to hold information about promotions
				$promotion_query = "CREATE TABLE $this->promotion_table (
									id INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
									promotion VARCHAR( 255 ) NOT NULL ,
									description TEXT NULL ,
									conditions TEXT NULL ,
									date_start DATE NOT NULL ,
									date_end DATE NOT NULL ,
									banner VARCHAR( 255 ) NULL ,
									active INT( 3 ) NOT NULL Default '0' )";
							
				dbDelta($promotion_query);
				$wpdb->query( "INSERT INTO $this->promotion_table (promotion, description, conditions, date_start, date_end, banner, active) VALUES ('No Project', 'No Project', 'No Project', '', '', '', 0)" );
			} // End project table existence check
			
			// Make sure the participants table doesn't already exist
			if( $wpdb->get_var( "SHOW TABLES LIKE '$this->participants_table'" ) != $this->participants_table ) {
				
				// Create the table to hold information about participants
				$participants_query = "CREATE TABLE $this->participants_table (
								id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
								idpromotion INT( 11 ) NULL ,
								idfacebook INT( 22 ) NULL ,
								username VARCHAR( 255 ) NULL ,
								link VARCHAR( 255 ) NULL ,
								code INT( 11 ) NULL ,
								date DATE NULL )";
				
				dbDelta($participants_query);
				$wpdb->query( "INSERT INTO $this->participants_table (client_id, client_name, client_email, client_site, client_description) VALUES (-1, 'No Client', '', '', '')" );
			} // End client table existence check
			
			// Make sure the results table doesn't already exist
			if( $wpdb->get_var( "SHOW TABLES LIKE '$this->results_table'" ) != $this->results_table ) {
				
				// Create the table to hold information about results
				$results_query = "CREATE TABLE $this->results_table (
								id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
								idpromotion INT( 11 ) NULL ,
								idfacebook INT( 22 ) NULL ,
								username VARCHAR( 255 ) NULL ,
								link VARCHAR( 255 ) NULL ,
								code INT( 11 ) NULL ,
								date DATE NULL ) ";
										
				dbDelta($results_query);
			} // End task table existence check
		
			
			// If the version option doesn't exist, then add it.  Otherwise update it.
			if( FALSE === get_option( 'WP-FBapp-Promotion Version' ) ) {
				add_option( 'WP-FBapp-Promotion Version', $this->version );
			} else {
				update_option( 'WP-FBapp-Promotion Version', $this->version );
			}
			
			if( FALSE === get_option( 'WP-FBapp-Promotion Options' ) ) {
				add_option( 'WP-FBapp-Promotion Options', serialize( array( 'current_timer' => -1, 'timer_started' => 0 ) ) );
			} else {
				update_option( 'WP-FBapp-Promotion Options', serialize( array( 'current_timer' => -1, 'timer_started' => 0 ) ) );
			}
		}
	
		/**
		 * Completely remove all data and database tables concerned with the WP-FBapp-Promotion plugin.  This function should be
		 * called only after the user is warned several times of what will happen if they proceed with this action.
		 * All data that they have entered will be erased permanently and will be unretrievable.
		 */
		function uninstall() {
			global $wpdb;
			$wpdb->show_errors();
			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			
			// Make sure the promotion table exists
			if( $wpdb->get_var( "SHOW TABLES LIKE '$this->promotion_table'" ) == $this->promotion_table ) {
				$wpdb->query( "DROP TABLE {$this->promotion_table}" );
			}
			
			// Make sure the participants table exists
			if( $wpdb->get_var( "SHOW TABLES LIKE '$this->participants_table'" ) == $this->participants_table ) {
				$wpdb->query( "DROP TABLE {$this->participants_table}" );
			}
			
			// Make sure the results table exists
			if( $wpdb->get_var( "SHOW TABLES LIKE '$this->results_table'" ) == $this->results_table ) {
				$wpdb->query( "DROP TABLE {$this->results_table}" );
			}
			
			// If the version option exists, delete it.
			if( FALSE !== get_option( 'WP-FBapp-Promotion Version' ) ) {
				delete_option( 'WP-FBapp-Promotion Version' );
			}
		}
		
		
		/**
		 * Retrieves an optionally paginated list of promotions.
		 * 
		 * @returns array An array of promotions that are in the system.
		 */
		function get_promotions( $page = null ) {
			global $wpdb;
			
			$query = "SELECT id, promotion, description, conditions, date_start, date_end, banner, active FROM $this->promotion_table WHERE id >= 0";
			
			return $wpdb->get_results($query, OBJECT);
		}
		
		/**
		 * Returns a single promotion, as identified by its id.
		 *
		 * @param int $id the unique id for the promotion being searched for.
		 * @return array a promotion from the database.
		 */
		function get_promotion( $id ) {
			global $wpdb;
			
			$query = "SELECT project_id, project_title, project_description, P.client_id, client_name FROM $this->project_table P, $this->client_table C WHERE P.client_id = C.client_id AND project_id = " . $wpdb->escape( $id );
			
			return $wpdb->get_row( $query, ARRAY_A );
		}
		
		/**
		 * Determines whether or not a project is currently being edited.
		 *
		 * @return array|bool an array describing the current promotion or false if a promotion isn't being edited.
		 */
		function is_editing_promotion( ) {
			if( $_GET[ 'action' ] == 'edit' && ( $promotion = $this->get_promotion( $_GET[ 'id' ] ) ) !== FALSE ) {
				return $promotion;
			} else {
				return FALSE;
			}
		}
		
		/**
		 * Displays the dashboard for the WP-FBapp-Promotion plugin.
		 */
		function top_level_page() { 
			if( $this->is_installed ) {
				include( dirname( __FILE__ ) . '/template/dashboard.php' );
			} else {
				echo '<div class="wrap"><p>WP-FBapp-Promotion is uninstalled.  Please deactivate the plugin.</p></div>';
			}
		}
		
		/**
		 * Displays the promotion page for the WP-FBapp-Promotion plugin.
		 */
		function promotions_page() { 
			include( dirname( __FILE__ ) . '/template/promotion.php' );
		}
	
		/**
		 * Displays the participants page for the WP-FBapp-Promotion plugin.
		 */
		function participants_page() { 
			include( dirname( __FILE__ ) . '/template/participants.php' );
		}
	
		/**
		 * Displays the results page for the WP-FBapp-Promotion plugin.
		 */
		function results_page() { 
			include( dirname( __FILE__ ) . '/template/results.php' );
		}
		
		/**
		 * Displays the results page for the WP-FBapp-Promotion plugin.
		 */
		function raffle_page() { 
			include( dirname( __FILE__ ) . '/template/raffle.php' );
		}
	
		/**
		 * Truncates a string if it is longer than 125 characters.  Adds 
		 * ellipses if the string is longer than it should be.
		 *
		 * @param string $string the string to truncate.
		 * @return string the truncated string.
		 */
		function truncate( $string, $length = 125 ) {
			return ( strlen( $string ) > $length ? substr( $string, 0, $length ) . '...' : $string );
		}
		
		/**
		 * Prints paginated rows of promotion for use in the promotion table.
		 *
		 * @param int $page the page to use for pagination.
		 */
		function promotion_rows( $page = null ) {
			$promotions = $this->get_promotions( $page );
			
			foreach( $promotions as $promotion ) {
				$class = ( $class == 'alternate' ? '' : 'alternate' );
			?>
			<tr class="<?php echo $class; ?>" id="project_row-<?php echo $promotion->id; ?>">
				<th class="check-column" scope="row"><input id="promotion_cb-<?php echo $promotion->id; ?>" name="promotion_cb[<?php $promotion->id; ?>]" type="checkbox" value="<?php echo $promotion->id; ?>" /></th>
				<td><a href="<?php $this->friendly_page_link( 'promotions' ); ?>&amp;action=edit&amp;id=<?php echo $promotion->id; ?>"><?php echo $promotion->promotion; ?></a></td>
				<td><?php echo $promotion->date_end; ?></td>
				<td><?php echo $this->truncate( $promotion->description ); ?></td>
			</tr>
			<?php	
			}
		}
	
		/**
		 * Returns or displays a friendly page slug.
		 *
		 * @param string $slug_id The string identifying the page to be referenced.
		 * @param bool $display Whether to return or display the value.
		 */
		function friendly_page_slug( $slug_id, $display = true ) {
			if( isset( $this->page_slugs[ $slug_id ] ) ) {
				$array = explode( '_page_', $this->page_slugs[ $slug_id ] );
				if( $display ) {
					echo $array[ 1 ];
				} else {
					return $array[ 1 ];
				}
			} else if( $slug_id == 'top_level' ) {
				if( $display ) {
					echo 'wp_fbapp_promotion';
				} else {
					return 'wp_fbapp_promotion';
				}
			}
		}
		
		/**
		 * Returns or display a friendly link between pages in WP-FBapp-Promotion . 
		 *
		 * @param string $slug_id the id for the page to be displayed.
		 * @param bool $display Whether to display or return the value.
		 */
		function friendly_page_link( $slug_id, $display = true ) {
			$page_slug = $this->friendly_page_slug( $slug_id, false );
			
			$value = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . $page_slug;
			if( $display ) {
				echo $value;
			} else {
				return $value;
			}
		}
	
	
	}

	
}
   
// Ensure the class exists before instantiating an object of this type
if( class_exists( 'wp_fbapp_promotion' ) ) {
	
	$wp_fbapp_promotion = new wp_fbapp_promotion();
	
	// Activation and Deactivation
	register_activation_hook( __FILE__, array( &$wp_fbapp_promotion, 'on_activate' ) );
	register_deactivation_hook( __FILE__, array( &$wp_fbapp_promotion, 'on_deactivate' ) );
	
	// Actions
	add_action( 'admin_menu', array( &$wp_fbapp_promotion, 'on_admin_menu' ) );
	add_action( 'admin_head', array( &$wp_fbapp_promotion, 'on_admin_head' ) );
	add_action( 'init', array( &$wp_fbapp_promotion, 'on_init' ) );
	add_action( 'wp_ajax_timer_toggle', array( &$wp_fbapp_promotion, 'on_timer_toggle' ) );
	
	// Filters
	
}

   
?>