<?php
if ( ! class_exists( 'cliff_fpe_activate' ) ) {

	class cliff_fpe_activate {

  		const CRON_HOOK = 'cliff_fpe_page_scrape';
  		const DEFAULT_CRON_SCHEDULE = 'twicedaily';

		public function activate() {

			$current_cron = wp_get_schedule( $this::CRON_HOOK );

	    	if( $current_cron ) {

	    		return false;

	    	} else {

	    		wp_schedule_event( time(), $this::DEFAULT_CRON_SCHEDULE, $this::CRON_HOOK);
	    		wp_schedule_event( time(), $this::DEFAULT_CRON_SCHEDULE, $this::CRON_HOOK . "_ov", array("ov"=>true) );
	    		add_action(  $this::CRON_HOOK, array($this, 'do_cron'), 10, 1 );

	    	}

		}

	    public function do_cron( $args ) {

	    	$cliff_fpe_cron = new cliff_fpe_cron( $args );

	    }

		static function check_deps() {
			/**
			* Check if The Events Calendar is active
			**/
			if ( !in_array( 'the-events-calendar/the-events-calendar.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				
				// Deactivate the plugin
				deactivate_plugins(__FILE__);
				
				// Throw an error in the wordpress admin console
				$error_message = __('This plugin requires <a href="https://wordpress.org/plugins/the-events-calendar/">The Events Calendar</a> plugin to be active!', 'the-events-calendar');
				die($error_message);
				
			}
			
		}

	}

}

?>