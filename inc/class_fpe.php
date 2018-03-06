<?php
if ( ! class_exists( 'cliff_fpe' ) ) {
  class cliff_fpe {

  		private $options;

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
		    wp_enqueue_script( 'fpe_scripts', plugin_dir_url( __FILE__ )  . '../js/fpe_main.js', array("jquery"), false, true );
		    wp_localize_script( 'fpe_scripts', 'ajax_object', array( 'ajax_url' => admin_url('admin-ajax.php') , 'we_value' => 1234 ) );
		    wp_enqueue_style('fpe-styles', plugin_dir_url( __FILE__ ) . '../css/fpe_main.css');
		    
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

?>