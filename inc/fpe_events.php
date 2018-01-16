<?php
defined( 'ABSPATH' ) or die( 'Noop' );

if ( ! class_exists( 'cliff_fpe_events' ) ) {

	class cliff_fpe_events {

		public $event_array;
		public $cliff_fpe_helpers;
		public $data;
		public $ov;
		public $post_types;

		public function __construct( $data = null, $ov = null ) {

			$this->cliff_fpe_helpers = new cliff_fpe_helpers();
			$this->data = isset($_POST["data"]) ? $_POST["data"] : $this->cliff_fpe_helpers->object_to_array($data);
			$this->ov = $ov ? $ov : null;
			$this->post_types = [
				["venue", "Venue"],
				["organizer", "Organizer"],
				["event", "Event"],
			];


			if( $this->data["id"] ) {

				$this->event_array = $this->contruct_data_arrays();
				$this->add_post_and_meta();	

			}	

		}

		/**
		 * Build main data arrays that will be used to add or update
		 * posts and meta to the Wordpress DB
		 *
		 *
		 * @return array
		 */

		public function contruct_data_arrays() {

			$arrays = $event_array = $venue_array = $organizer_array = [];

			if( $this->ov == "venue" ) {

				$venue_array = $this->construct_venue_array();

			} elseif( $this->ov == "organizer" ) {

				$organizer_array = $this->construct_organizer_array();

			} else {

				$event_array = $this->construct_event_array();
				$venue_array = $this->construct_venue_array();
				$organizer_array = $this->construct_organizer_array();	

			}

			$arrays["event"] = $event_array ? $event_array : null;
			$arrays["organizer"] = $organizer_array ? $organizer_array : null;
			$arrays["venue"] = $venue_array ? $venue_array : null;

			return $arrays;

		}

		/**
		 * Build event array that will be used to add or update posts
		 * and meta in the Wordpress DB
		 *
		 *
		 * @return array
		 */

		public function construct_event_array() {

			$data = $this->data;
			if( !$data["start_time"] ) return false;

			$timezone = isset( $data["timezone"] ) ? $data["timezone"] : $this->cliff_fpe_helpers->get_timezone( $data["start_time"] );

			$event_array = [
				"post_title"		=> $data["name"],
				"post_content"		=> $data["description"],
				"post_status"		=> "publish",
				"post_type"			=> "tribe_events",
				"post_meta"			=> [
					"_EventFacebookID"		=> $data["id"],
					"_EventShowMapLink"		=> 1,
					"_EventShowMap"			=> 1,
					"_EventStartDate"		=> $this->cliff_fpe_helpers->convert_fb_date( $data["start_time"], $timezone ),		
					"_EventStartDateUTC"	=> $this->cliff_fpe_helpers->convert_fb_date_utc( $data["start_time"] ),
					"_EventDuration"		=> 0,
					"_EventURL"				=> "https://facebook.com/" . $data["id"],
					"_EventTimezone"		=> $timezone,
					"_EventTimezoneAbbr"    => $this->cliff_fpe_helpers->get_timezone_abbreviation( $timezone ),
					"_EventVenueID"    		=> null, // updateme
					"_EventOrganizerID"		=> null, // updateme
					"_EventUpdatedDate"		=> $data["updated_time"] // updateme
				]
			];

			if( array_key_exists( "end_time", $data ) ) {

				$event_array["post_meta"]["_EventEndDate"] 		= $this->cliff_fpe_helpers->convert_fb_date( $data["end_time"], $timezone );
				$event_array["post_meta"]["_EventEndDateUTC"]	= $this->cliff_fpe_helpers->convert_fb_date_utc( $data["end_time"] );
				$event_array["post_meta"]["_EventDuration"]		= $this->cliff_fpe_helpers->get_event_duration( $data["start_time"], $data["end_time"] );

			}

			if( isset( $data["cover"] ) ) {

				$event_array["post_meta"]["_EventCover"] 		= isset( $data["cover"]["source"] ) ? $data["cover"]["source"] : $data["cover"]->source;

			}

			if( array_key_exists( "ticket_uri", $data ) ) {

				$event_array["post_meta"]["_EventTicketUrl"]	= $data["ticket_uri"];

			}

			return $event_array;

		}

		/**
		 * Build venue array that will be used to add or update posts
		 * and meta in the Wordpress DB
		 *
		 *
		 * @return array
		 */

		public function construct_venue_array() {

			$data = $this->data;
			if( $this->ov ) $data["place"] = $data;

			$venue_array = [
				"post_title"		=> $data["place"]["name"],
				"post_status"		=> "publish",
				"post_type"			=> "tribe_venue",
				"post_meta"			=> [
					"_VenueFacebookID"		=> $data["place"]["id"],
					"_VenueShowMapLink"		=> 1,
					"_VenueShowMap"			=> 1,
					"_VenueOrigin"			=> "events-calendar",
					"_VenueAddress"			=> isset( $data["place"]["location"]["street"] ) ? $data["place"]["location"]["street"] : '',
					"_VenueCity"			=> $data["place"]["location"]["city"],
					"_VenueCountry"			=> $data["place"]["location"]["country"],
					"_VenueProvince"		=> null, // updateme
					"_VenueStateProvince"	=> null, // updateme
					"_VenueZip"				=> $data["place"]["location"]["zip"],
					"_VenuePhone"			=> null, // updateme
				]
			];

			if( array_key_exists( "state", $data["place"]["location"] ) ) { 

				$venue_array["post_meta"]["_VenueState"] = $data["place"]["location"]["state"];

			}

			return $venue_array;
		}

		/**
		 * Build org array that will be used to add or update posts
		 * and meta in the Wordpress DB
		 *
		 *
		 * @return array
		 */

		public function construct_organizer_array() {

			$data = $this->data;
			if( $this->ov ) $data["owner"] = $data;

			if( array_key_exists( "owner", $data ) ) { 

				$organizer_array = [
					"post_title"  		=> $data["owner"]["name"],
					"post_status"		=> "publish",
					"post_type"			=> "tribe_organizer",
					"post_meta"			=> [
						"_OrganizerFacebookID"	=> $data["owner"]["id"],
						"_OrganizerOrigin"		=> "events-calendar",
					]
				];

			}

			return $organizer_array;

		}

		/**
		 * Add or update posts and then post meta
		 *
		 * @return void
		 */

		public function add_post_and_meta() {

			$event_array = $this->event_array;

			if( $this->ov ) {

				foreach ($this::POST_TYPE as $pt) {

					if( $pt[0] == $this->ov ) $post_type[0] = $pt;

				}

			} else {

				$post_type = $this::POST_TYPE;

			}

			foreach ($post_type as $p) {

				$facebook_id = $event_array[$p[0]]["post_meta"]["_" . $p[1] . "FacebookID"];
				$post_id = $this->get_post_id_from_fb_id( $facebook_id, $p[1]);
				if( $post_id ) $event_array[$p[0]]["ID"]  = $post_id;
				$post_data = $event_array[$p[0]];
				unset( $post_data["post_meta"] );


				if ( !$post_id && ( $p[0] != "event" ) ) {

					// This is a new venue or org, get its extra meta data and add it to the DB

					$this->event_array = $event_array;
					$id = $this->wp_insert_update_post( $post_data );

					if( ! $this->ov ) {
						$event_array = $this->get_extra_facebook_meta( $facebook_id, $p );
						$event_array["event"]["post_meta"]["_Event" . $p[1] . "ID"] = $id[0];
					}

					$this->add_update_post_metas( $event_array[$p[0]]["post_meta"], $id[0] );

				} elseif ( $post_id && ( $p[0] != "event")) {

					// This is an existing venue or org. 

					$event_array["event"]["post_meta"]["_Event" . $p[1] . "ID"] = $post_id;
					if( $this->ov ) $this->wp_insert_update_post( $post_data );
					$this->add_update_post_metas( $event_array[$p[0]]["post_meta"], $post_id );

				} elseif ( $p[0] == "event" ) {

					// This is an event. Add or update it
					$this->event_array = $event_array;
					$post_data = $event_array[$p[0]];
					unset( $post_data["post_meta"] );
					$id = $this->wp_insert_update_post( $post_data );
					if( $id[1] == "insert") $this->attach_fb_image( $event_array, $id[0] );
					$this->add_update_post_metas( $event_array[$p[0]]["post_meta"], $id[0] );

				}

			}

		}

		/**
		 * Attach the event image to the post from it's URL. However if a similar one exists
		 * in the media library use that
		 *
		 * @param array $event_array an event array
		 * @param int $post_id a Wordpress post id
		 *
		 * @return void
		 */

		static function attach_fb_image( $event_array, $post_id ) {

			return false;

			global $wpdb;

			// media_sideload_image($file, $post_id, $desc, $return)

			// Dont run this on cron jobs where image exists it's too heavy 

			if( $_POST["action_type"] == "cron" && has_post_thumbnail( $post_id )) return false;

			// this has an image already, has it changed?

			if( has_post_thumbnail( $post_id ) ) {

				$thumbnail_id = get_post_thumbnail_id( $post_id );
				$img_url = wp_get_attachment_url( $thumbnail_id );

				$sql = $wpdb->prepare(
				    "SELECT post_id FROM $wpdb->postmeta 
				         WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
				    $img_url
				);
				$post_id = $wpdb->get_var( $sql );

			}
		}

		/**
		 * add a post or update it if the ID exists
		 *
		 * @param array $post_data, a Wordpress post object
		 *
		 * @return a post ID or false
		 */

		static function wp_insert_update_post( $post_data ) {

			if( array_key_exists("ID", $post_data) ) {

				echo $post_data["post_title"] . " <span class=\"info\">Updated</span><br>";

				return [wp_update_post( $post_data ), "update"];

			} else {

				if( $_POST["action_type"] == "cron") {

					echo $post_data["post_title"] . " Added\n";

				} else {

					echo $post_data["post_title"] . " <span class=\"info\">Added</span><br>";

				}

				return [wp_insert_post( $post_data ), "insert"];

			}
		}


		/**
		 * Does a Facebook post_meta entry exist with the specfied Facebook ID. Essentially
		 * is the Facebook ID event, org or venue already exist?
		 *
		 * @param string $fb_id facebook venue or org id such as 12345678
		 * @param string $meta_prefix either Venue, Oranizer or Event
		 *
		 * @return a post ID or false
		 */

		static function get_post_id_from_fb_id( $fb_id, $meta_prefix ) {

			global $wpdb;

			$result = $wpdb->get_results( "select * from $wpdb->postmeta where meta_key = '_" . $meta_prefix . "FacebookID' and meta_value = '" . $fb_id . "'" );

			return empty( $result ) ? false : $result[0]->post_id;

		}

		/**
		 * Get extra Facebook meta for venues and org that cannot be got from the event graph call
		 *
		 *
		 * @param string $fb_id facebook venue or org id such as 12345678
		 *
		 * @return void
		 */

		public function get_extra_facebook_meta( $fb_id, $p ) {

			$event_array = $this->event_array;
			$fields = "phone,cover,description,website";

			$extra_meta = $this->cliff_fpe_helpers->get_facebook_json( $fb_id,"fields=" . $fields, $this->data );
			if( property_exists($extra_meta, "description")) $event_array[$p[0]]["post_content"] = $extra_meta->description;
			if( property_exists($extra_meta, "website")) $event_array[$p[0]]["post_meta"]["_" . $p[1] . "Website"] = $extra_meta->website;
			if( property_exists($extra_meta, "cover")) $event_array[$p[0]]["post_meta"]["_" . $p[1] . "Cover"] = $extra_meta->cover->source;
			if( property_exists($extra_meta, "phone")) $event_array[$p[0]]["post_meta"]["_" . $p[1] . "Phone"] = $extra_meta->phone;

			return $event_array;

		}

		/**
		 * Get extra Facebook meta for venues and org that cannot be got from the event graph call
		 *
		 *
		 * @param array $post_metas an array of post meta values [string $meta_key, string $meta_value ]					
		 * @param int Wordpress post ID				
		 *
		 * @return void
		 */

		static function add_update_post_metas( $post_metas, $post_id ) {

			foreach ($post_metas as $key => $pm) {

				if( get_post_meta( $post_id, $key, true ) !== false ) {

					$id = update_post_meta( $post_id, $key, $pm );

				} else {

					$id = add_post_meta( $post_id, $key, $pm, true );

				}
			}

		}

	}

}

?>