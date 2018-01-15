<?php

defined( 'ABSPATH' ) or die( 'Noop' );

if ( ! class_exists( 'cliff_fpe_helpers' ) ) {
	 
	class cliff_fpe_helpers {

		public function __construct() {
			
		}

		const DATE_FORMAT = 'Y-m-d H:i:00';
		const FB_GRAPH_URL = 'https://graph.facebook.com';

		public function post_url($url, $params) {

		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, null, '&'));
		    $ret = curl_exec($ch);
		    curl_close($ch);
		    return $ret;

		}

		public function get_facebook_json($fb_id, $params, $token = null) {

			if( !$token ) $token = $this->get_app_access_token();
			$url = $this::FB_GRAPH_URL . "/" . $fb_id . "?access_token=" . $token . "&" . $params;
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			return json_decode($data);

		}

		public function get_app_access_token() {

			$this->options = get_option( 'fb_app_option_name' );
			$url = $this::FB_GRAPH_URL . '/oauth/access_token';
			$token_params = array(
			    "type" => "client_cred",
			    "client_id" => $this->options['fb_app_id'],
			    "client_secret" => $this->options['fb_app_secret']
		    );
			$token = json_decode($this->post_url($url, $token_params));
			return isset($token->access_token ) ? $token->access_token : false;

		}  

		public function convert_fb_date( $date, $timezone = "UTC" ) {

			$date = DateTime::createFromFormat(DateTime::ISO8601, $date);
			//$date = new DateTime($date, new DateTimeZone($timezone) );
			$date =  $date->format($this::DATE_FORMAT);
			return $date ? $date : null;

		}	

		public function convert_fb_date_utc( $date ) {

			$date = $this->convert_fb_date( $date );
			$date = new DateTime($date, new DateTimeZone('UTC'));
			return $date->format($this::DATE_FORMAT);

		}

		public function get_timezone( $date ) {

			$date = DateTime::createFromFormat(DateTime::ISO8601, $date);
			return $hdate->getTimezone();

		}

		public function get_event_duration( $start_time, $end_time) {

			return strtotime( $end_time ) - strtotime( $start_time );

		}

		static function get_timezone_abbreviation( $timezone ) {

		    if( $timezone ) {
		        $abb_list = timezone_abbreviations_list();

		        $abb_array = array();
		        foreach ($abb_list as $abb_key => $abb_val) {
		            foreach ($abb_val as $key => $value) {
		                $value['abb'] = $abb_key;
		                array_push($abb_array, $value);
		            }
		        }

		        foreach ($abb_array as $key => $value) {
		            if($value['timezone_id'] == $timezone){
		                return strtoupper($value['abb']);
		            }
		        }
		    }

		}

		public function object_to_array( $obj ) {

		    if(is_object($obj)) $obj = (array) $obj;
		    if(is_array($obj)) {
		        $new = array();
		        foreach($obj as $key => $val) {
		            $new[$key] = $this->object_to_array($val);
		        }
		    }
		    else $new = $obj;
		    return $new;       
		}

		public function remote_filesize($url) {
		    static $regex = '/^Content-Length: *+\K\d++$/im';
		    if (!$fp = @fopen($url, 'rb')) {
		        return false;
		    }
		    if (
		        isset($http_response_header) &&
		        preg_match($regex, implode("\n", $http_response_header), $matches)
		    ) {
		        return (int)$matches[0];
		    }
		    return strlen(stream_get_contents($fp));
		}


	}

}
?>