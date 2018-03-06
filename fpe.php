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
include_once "inc/fpe_cron.php";
include_once "inc/fpe_shortcodes.php";
include_once "inc/fpe_events.php";
include_once "inc/fpe_helpers.php";
include_once "inc/class_fpe.php"; 


add_action('init','init_functions');

global $cliff_fpe_register;

function init_functions() {

	add_action( "cliff_fpe_page_scrape", array( 'cliff_fpe_cron', 'init' ));

	if( !array_key_exists("event_actions", $_POST) ) { 

		$cliff_fpe_shortcodes = new cliff_fpe_shortcodes();

		if( is_admin() ) {
			$cliff_fpe = new cliff_fpe();
			$cliff_fpe_helpers = new cliff_fpe_helpers();
			$cliff_fpe_options = new cliff_fpe_options();       
		}

	} else {
		
		
		if( $_POST["action_type"] == "cron") {

			cliff_fpe_cron::init(); 

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
