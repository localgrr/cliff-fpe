<?php
//defined( 'ABSPATH' ) or die( 'Noop' );

if ( ! class_exists( 'cliff_fpe_shortcodes' ) ) {

	class cliff_fpe_shortcodes {

		public function __construct() {

			add_shortcode( 'fpe_cover_image', array( $this, 'fpe_shorcode_cover' ) );
			add_shortcode( 'fpe_ticket_button', array( $this, 'fpe_shorcode_ticket_button' ) );
			add_shortcode( 'fpe_gotd', array( $this, 'fpe_shortcode_gotd' ) );
			add_shortcode( 'fpe_events_list', array( $this, 'fpe_events_list' ) );
		}

		public function get_template( $name ) {

			$file = $name . ".php";
			$template = "../templates/" . $file;
			$template_override = get_stylesheet_directory() . "/fpe/templates/" . $file;

			if(file_exists($template_override)) {

				include_once $template_override;

			} else {

				include_once $template;

			}

		}

		public function fpe_events_list( $atts ) {

			global $wpdb;
			//_EventStartDate
			$today = date("Y-m-d 00:00:00");

			$args = array(
			    'posts_per_page'  => -1,
			    'orderby'         => 'meta_value',
			    'meta_key'    => '_EventStartDate',
			    'order'           => 'ASC',
			    'post_type'       => 'tribe_events',
			    'meta_query' => array(
			      array(
			        'key' => '_EventStartDate',
			        'value' => date("Y-m-d H:i:00"),
			        'compare' => '>=',
			        'type' => 'DATE'
			        )
			      )
			    ); 

			$posts = get_posts( $args );

			if( count($posts) < 1 ) {
				$this->get_template("sorry-no-events");
			}

			foreach ($posts as $p) {
				$p->post_meta = get_post_meta( $p->ID );
			}
			
			//pre_r($posts); 

		}

		/**
		 * Add a shortcode to add a cover for a facebook event should it exist
		 *
		 * @param array $post_data, a Wordpress post object
		 *
		 * @return HTML
		 */

		static function fpe_shorcode_cover( $atts ) {

			global $post;
			$url = get_post_meta( $post->ID, '_EventCover', true );
			return $url ? '<img src="' . $url . '" class="fpe-event-cover">' : "";

		}

		/**
		 * Add a shortcode to add a cover for a facebook event should it exist
		 *
		 * @param array $post_data, a Wordpress post object
		 *
		 * @return HTML
		 */

		static function fpe_shorcode_ticket_button( $atts ) {

			global $post;
			$url = get_post_meta( $post->ID, '_EventTicketUrl', true );
			return $url ? '<a class="tribe-events-button fpe-get-tickets-button" href="' . $url . '">Get Tickets</a>' : '';
		}

		/**
		 * Add a shortcode to add a cover for a facebook event should it exist
		 *
		 * @param array $post_data, a Wordpress post object
		 *
		 * @return HTML
		 */

		static function fpe_shortcode_gotd( $atts ) {

			global $post;
			$url = get_post_meta( $post->ID, '_EventTicketUrl', true );
			return $url ? '<a class="tribe-events-button" href="' . $url . '">Get Tickets</a>' : '';
		}

	}

}

?>