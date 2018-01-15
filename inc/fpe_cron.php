<?php

defined( 'ABSPATH' ) or die( 'Noop' );

if ( ! class_exists( 'cliff_fpe_cron' ) ) {

	class cliff_fpe_cron {

		private $options;
		private $token;
		private $cliff_fpe_helpers;
		private $cliff_fpe_events;

		const POST_TYPE = [
			["venue", "Venue"],
			["organizer", "Organizer"]
		];

		public function __construct() {

			$this->options = get_option( 'general_settings_option_name' );
			$this->token = $_POST["token"];
			$this->options["facebook_page_ids"] = explode( "\n", $this->options["facebook_page_ids"] );
			$this->cliff_fpe_helpers = new cliff_fpe_helpers(); 

			if( isset( $_POST["ovs"] )) {

				$this->scrape_ovs();

			} else {

				$this->scrape_page( $this->options["facebook_page_ids"] );

			}
			
		}

		/**
		 * Scrape facebook page for event IDs and add them to Wordpress
		 *
		 * @param array $page_ids, an array of Facebook page IDs
		 *
		 * @return false if invalid
		 */

		public function scrape_page( $page_ids ) {

			$this->add_ids( $page_ids );
		}

		public function scrape_ovs() {

			global $wpdb;

			$ids_clean = [];
			$post_type = $this::POST_TYPE;
			

			foreach ($post_type as $p) {

				$post_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'tribe_" . $p[0] . "' and post_status = 'publish'" );
				$ids_clean[$p[0]] = [];

				foreach ($post_ids as $id) {
					
					$facebook_id = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '" . $id->ID . "' AND meta_key = '_" . $p[1] . "FacebookID'" );
					array_push($ids_clean[$p[0]], $facebook_id);

				}
				array_unique($ids_clean[$p[0]]);
				$this->add_ids( $ids_clean[$p[0]], $p[0] );

			}

		}

		public function add_ids( $page_ids, $ov = null ) {

			$fields = $ov ? "cover,description,phone,website,name,location" : "cover,description,end_time,start_time,name,owner,place,ticket_uri,timezone,updated_time,is_canceled&time_filter=upcoming";
			$events = $ov ? "" : "/events";

			foreach ($page_ids as $id) {

				$id = preg_replace( "/\r|\n/", "", $id );
				$fb_json = $this->cliff_fpe_helpers->get_facebook_json($id . $events, "fields=" . $fields, $this->token );
				
				if( isset( $fb_json->data )) {

					foreach ($fb_json->data as $data) {

						if( isset( $data->id) ) $cliff_fpe_events = new cliff_fpe_events( $data );

					}

				} else {

					if( isset( $fb_json->id) ) $cliff_fpe_events = new cliff_fpe_events( $fb_json, $ov );

				}
				


			}

		}

		static function clean_db() {
/*			
SELECT * FROM wp_postmeta pm LEFT JOIN wp_posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL;
DELETE pm FROM wp_postmeta pm LEFT JOIN wp_posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL;
*/
		}

	}

}

?>