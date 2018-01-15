<?php
//defined( 'ABSPATH' ) or die( 'Noop' );

if ( ! class_exists( 'cliff_fpe_shortcodes' ) ) {

	class cliff_fpe_shortcodes {

		public function __construct() {

			add_shortcode( 'fpe_cover_image', array( $this, 'fpe_shorcode_cover' ) );
			add_shortcode( 'fpe_ticket_button', array( $this, 'fpe_shorcode_ticket_button' ) );
			add_shortcode( 'fpe_gotd', array( $this, 'fpe_shortcode_gotd' ) );

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