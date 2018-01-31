<?php
//defined( 'ABSPATH' ) or die( 'Noop' );

/**
 * @package fpe
 * @version 1.0
 */
/*
Plugin Name: Facebook Page Events
Plugin URI: https://cliffweb.eu/fpe
Description: A plugin that allows Facebook to interface with Tribe's Event Calendar
Author: Caroline Clifford
Version: 1.0
Author URI: https://cliffweb.eu
Text Domain: cliff_fpe
*/ 

if( !defined('SHORTINIT') ) define( 'SHORTINIT', true );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
define('FPE_PLUGIN_PATH', WP_PLUGIN_DIR . '/cliff-fpe/');


include_once "inc/third-party/JP_admin_notices.php";
include_once "inc/fpe_options.php";
include_once "inc/fpe_shortcodes.php";
include_once "inc/fpe_events.php";
include_once "inc/fpe_helpers.php";
include_once "inc/fpe_cron.php";


if ( ! class_exists( 'cliff_fpe' ) ) {
  class cliff_fpe {

  		private $options;

  		const CRON_HOOK = 'cliff_fpe_page_scrape';
  		const DEFAULT_CRON_SCHEDULE = 'twicedaily';

	    public function __construct()
	    {			
			add_action( 'admin_print_scripts', array( $this, 'admin_inline_js' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_action( 'wp_ajax_my_action', array( $this, 'ajax_action' ) ); 
	    }

		function ajax_action() {

			$cliff_fpe = new cliff_fpe();
			$cliff_fpe_helpers = new cliff_fpe_helpers();
			$cliff_fpe_options = new cliff_fpe_options();

		}

		static function load_scripts() {
			// Check for jQuery

		    if ( ! wp_script_is( 'jquery', 'enqueued' )) {

		        //Enqueue
		        wp_enqueue_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js' );

		    }
		    wp_enqueue_script( 'fpe_scripts', plugin_dir_url( __FILE__ )  . 'js/fpe_main.js', array("jquery"), false, true );
		    wp_localize_script( 'fpe_scripts', 'ajax_object', array( 'ajax_url' => admin_url('admin-ajax.php') , 'we_value' => 1234 ) );
		    wp_enqueue_style('fpe-styles', plugin_dir_url( __FILE__ ) . 'css/fpe_main.css');
		    
		}

		static function admin_inline_js() {

			$cliff_fpe_helpers = new cliff_fpe_helpers();
			$token = $cliff_fpe_helpers->get_app_access_token();

			echo '
			<script type="text/javascript">
				var fpe_fields = "cover,description,end_time,start_time,name,owner,place,ticket_uri,timezone,updated_time,is_canceled&include_canceled=false&time_filter=upcoming";
				var fpe_token = "' . $token . '";
				var plugin_dir = "' . plugin_dir_url( __FILE__ ) . '";
			</script>
			';
			if(!$token) jp_notices_add_error('Front Page Events : Invalid Facebook App details');

		}

	}
}

add_action('init','init_functions');

global $cliff_fpe_register;

function init_functions() {

	if( !array_key_exists("event_actions", $_POST) ) { 

		$cliff_fpe_shortcodes = new cliff_fpe_shortcodes();

		if( is_admin() ) {
			$cliff_fpe = new cliff_fpe();
			$cliff_fpe_helpers = new cliff_fpe_helpers();
			$cliff_fpe_options = new cliff_fpe_options();       
		}

	} else {
		
		
		if( $_POST["action_type"] == "cron") {

			$cliff_fpe_cron = new cliff_fpe_cron();

		} else {

			$cliff_fpe_events = new cliff_fpe_events();

		}

	}

}

function activate_cliff_fpe() {
	require_once plugin_dir_path( __FILE__ ) . 'inc/fpe_activate.php';
	$cliff_fpe_activate = new cliff_fpe_activate();
	$cliff_fpe_activate->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cliff-fpe-deactivator.php
 */
function deactivate_cliff_fpe() {
	//require_once plugin_dir_path( __FILE__ ) . 'includes/class-cliff-fpe-deactivator.php';
	//Cliff_Fpe_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cliff_fpe' );
//register_deactivation_hook( __FILE__, 'deactivate_cliff_fpe' );

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}



?>
