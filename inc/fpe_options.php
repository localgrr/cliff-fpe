<?php

defined( 'ABSPATH' ) or die( 'Noop' );

if ( ! class_exists( 'cliff_fpe_options' ) ) {
	
	class cliff_fpe_options {

		private $options;
		private $options2;

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'options_page_menu' ));
			add_action( 'admin_init', array( $this, 'options_page_init' ) );
		} 

		public function options_page_menu() {

			add_options_page( "Facebook Page Event Options", "Facebook Page Event Options", "administrator", "fpe-options", array( $this, 'options_page' ));
		}

		public function options_page() {
	        // Set class property
	        $this->options = get_option( 'fb_app_option_name' );
	        $this->options2 = get_option( 'general_settings_option_name' );

	        ?>
	        <div class="wrap">
	        <?php
	        	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general_settings';
	        ?>
	            <h1>Facebook Page Events Settings</h1>
				<h2 class="nav-tab-wrapper">
					<a href="?page=fpe-options&tab=general_settings" class="nav-tab<?php if($active_tab =="general_settings") echo ' nav-tab-active'; ?>">General Settings</a>
				    <a href="?page=fpe-options&tab=facebook_app_settings" class="nav-tab<?php if($active_tab =="facebook_app_settings") echo ' nav-tab-active'; ?>">Facebook App Settings</a>
				</h2>
	            <form method="post" action="options.php">
	            <?php
	            	
	                // This prints out all hidden setting fields

	                if( ($active_tab == "general_settings") && isset( $this->options['fb_app_id'] ) && isset( $this->options['fb_app_secret'] ) ) {
		                ?>
		                <div class="fpe-settings-group"><?php
			                settings_fields( 'general_settings_option_group' );
			                do_settings_sections( 'general-settings' );
			                submit_button(); ?>
			            </div>
			            <div class="fpe-info test"></div>
			            <div class="fpe-settings-group">
							<p>Instantly add events here. Existing entries will be updated</p>
							<textarea id="quick_add_ids" name="fb_app_option_name[quick_add_ids]"></textarea>
							<div class="textarea-button">
								<button id="fpa_add_events" class="button button-primary">Quick Add/Update Events Now</button>
							</div>
						</div>
						<div class="buttons-container">
							<button id="fpa_mimic_cron" class="button button-primary">Update all events now</button>
							<button id="fpa_mimic_cron_ovs" class="button button-primary">Update all Orgs/Venues now</button>
						</div>
						<div class="fpe-info"></div>

	            <?php
	                } else {
		                settings_fields( 'fb_app_option_group' );
		                do_settings_sections( 'fb-app-settings' );
		                submit_button();             	
	                }

	            ?>
	            </form>
	        </div>
	        <?php

		}

	    public function options_page_init()
	    {   
	    	/* ***
	    	 * FB App settings
	    	 * *** */

	        register_setting(
	            'fb_app_option_group', // Option group
	            'fb_app_option_name',
	            array($this, 'sanitize_text') // Sanitize
	        );

	        //sanitize_textarea_field( string $str )

	        add_settings_section(
	            'fb_app_section',
	            'Facebook App Settings. Get them from <a href="https://developers.facebook.com/apps/" target="_new">here</a>',
	            '',
	            'fb-app-settings'
	        );  

	        add_settings_field(
	            'fb_app_id',
	            'Facebook App ID', 
	            array( $this, 'fb_app_id_callback' ),
	            'fb-app-settings',
	            'fb_app_section'          
	        );      

	        add_settings_field(
	            'fb_app_secret', 
	            'Facebook App Secret', 
	            array( $this, 'fb_app_secret_callback' ), 
	            'fb-app-settings', 
	            'fb_app_section'
	        ); 

	    	/* ***
	    	 * General settings
	    	 * *** */

	        register_setting(
	            'general_settings_option_group', // Option group
	            'general_settings_option_name', // Option name
	            array($this, 'sanitize_textarea')
	        );

	        add_settings_section(
	            'general_settings_section',
	            '',
	            '',
	            'general-settings'
	        );  

	        add_settings_field(
	            'facebook_page_ids',
	            'Facebook Page Ids (one per line)', 
	            array( $this, 'facebook_page_ids_callback' ),
	            'general-settings',
	            'general_settings_section',
	            array('label_for' => 'facebook_page_ids')         
	        ); 

	        add_settings_field(
	            'facebook_cron_schedule',
	            'How often to scrape for events' , 
	            array( $this, 'facebook_cron_schedule_callback' ),
	            'general-settings',
	            'general_settings_section' ,
	            array('label_for' => 'facebook_cron_schedule')         
	        );        
	    }

	    static function sanitize_text( $fields ) {

	    	$key = key( $fields);
	    	$fields[$key] = sanitize_text_field( $fields[$key] );
	    	return $fields;

	    }

	    static function sanitize_textarea( $fields ) {

	    	$key = key( $fields);
	    	$fields[$key] = sanitize_textarea_field( $fields[$key] );
	    	return $fields;

	    }

	    public function facebook_page_ids_callback()
	    {

	    	$value = isset( $this->options2['facebook_page_ids'] ) ? $this->options2['facebook_page_ids'] : '';
	        echo '
	            <p>Add the ids (numerical or text) of Facebook pages that contain events to be added or updated at the specified interval</p>
	            <textarea id="facebook_page_ids" name="general_settings_option_name[facebook_page_ids]" required>'
	            . $value . '</textarea><div class="textarea-button"><button id="fpa_test_fb_ids" class="button button-primary">Test IDs</button></div>';
	    }

	    public function facebook_cron_schedule_callback()
	    {
	    	$selected = isset( $this->options2['facebook_cron_schedule'] ) ? esc_attr( $this->options2['facebook_cron_schedule']) : 'twicedaily';
	    	$selected_text = [
	    		"hourly" => "",
	    		"twicedaily" => "",
	    		"daily" => ""
	    	];
	    	$selected_text[$selected] = ' selected="selected"';

	        echo
	            '
	            <p>Please note this can be very server intensive so I\'d keep it to twice daily or more. This will not update past events, you can only do that using the quick updater.</p>
	            <select id="facebook_cron_schedule" name="general_settings_option_name[facebook_cron_schedule]" required>
	            	<option value="hourly"' . $selected_text["hourly"] . '>Hourly</option>
	            	<option value="twicedaily"' . $selected_text["twicedaily"]  . '>Twice Daily</option>
	            	<option value="daily"' . $selected_text["daily"] . '>Daily</option>
	            </select><p>Organizations and venues are automatically updated twice daily</p>';
	            
	        ;
	    }

	    public function fb_app_id_callback()
	    {
	        printf(
	            '<input type="text" id="fb_app_id" name="fb_app_option_name[fb_app_id]" value="%s" required="required" />',
	            isset( $this->options['fb_app_id'] ) ? esc_attr( $this->options['fb_app_id']) : ''
	        );
	    }

	    /** 
	     * Get the settings option array and print one of its values
	     */
	    public function fb_app_secret_callback()
	    {
	        printf(
	            '<input type="text" id="fb_app_secret" name="fb_app_option_name[fb_app_secret]" value="%s" required="required" />',
	            isset( $this->options['fb_app_secret'] ) ? esc_attr( $this->options['fb_app_secret']) : ''
	        );
	    }
	}
}
?>