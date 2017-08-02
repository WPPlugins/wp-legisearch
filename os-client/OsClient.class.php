<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( 'os-config.php' );
require_once( 'JsonCacheable.class.php' );

/**
 * Class for interacting with Open States API
 * This is coupled to Wordpress and the Legisearch Plugin for caching purposes.
 */
class OsClient {

	public $apikey = null;
	public $jsoncacheable = null;

	/**
	 * Default constructur
	*/
	public function __construct( $api_key, $json_cacheable ) {
		$this->apikey = $api_key;
		$this->jsoncacheable = $json_cacheable;
	}

	/**
	 * Call Metadata Overview method.
	 * Returns json object on success, or false if error
	 */
	public function get_metadata_overview( $fields = null ) {
		$url = OSAPI_URL_METADATAOVERVIEW . "?apikey={$this->apikey}" . $this->add_fields( $fields );
		return $this->jsoncacheable->get_json( $this->format_url( $url ) );
	}

	/**
	 * Calls State MetaData method.
	 * Returns json object on success, or false if error
	 */
	public function get_state_metadata( $state, $fields = null ) {
		$state = trim( strtolower( $state ) );	// It is picky about case.
		$url = OSAPI_URL_STATEMETADATA . "{$state}/?apikey={$this->apikey}" . $this->add_fields( $fields );
		return $this->jsoncacheable->get_json( $this->format_url( $url ) );
	}

	/**
	 * Calls Bill Search method.
	 * Returns json object on success, or false if error
	 */
	public function get_bill_search( $search_parameters, $fields = null) {
		$url = OSAPI_URL_BILLSEARCH . "/?apikey={$this->apikey}" . $this->add_search_parameters( $search_parameters ) . $this->add_fields( $fields );
		return $this->jsoncacheable->get_json( $this->format_url( $url ) );
	}

	/**
	 * Calls Bill Lookup method.
	 * Returns json object on success, or false if error
	 */
	public function get_bill_lookup( $state, $session, $bill_id, $fields = null ) {
		$state = trim( strtolower( $state ) );
		$session = trim( $session );
		$bill_id = $this->format_bill_id( trim( strtoupper( $bill_id ) ) );
		$url = OSAPI_URL_BILLLOOKUP . "{$state}/{$session}/{$bill_id}/?apikey={$this->apikey}" . $this->add_fields( $fields );
		return $this->jsoncacheable->get_json( $this->format_url( $url ) );
	}

	/**
	 * Calls Legislator search method.
	 * Returns json object on success, or false if error
  	 */
	public function get_legislator_search( $search_parameters, $fields = null ) {
		$url = OSAPI_URL_LEGISLATORSEARCH . "/?apikey={$this->apikey}" . $this->add_search_parameters( $search_parameters ) . $this->add_fields( $fields );
		return $this->jsoncacheable->get_json( $this->format_url( $url ) );
	}

	/**
	 * Calls the Legislator Geo Lookup method.
	 * Returns json object on success, or false if error
	*/
	public function get_legislator_geo( $lat, $lon, $fields = null ) {
		$url = OSAPI_URL_LEGISLATORGEO . "/?apikey={$this->apikey}&lat={$lat}&long={$lon}";
		return $this->jsoncacheable->get_json( $this->format_url( $url ) );
	}

	/**
	 * Calls the Legislator Lookup method.
	 */
	public function get_legislator_lookup( $id ) {
		$url = OSAPI_URL_LEGISLATORLOOKUP . "/{$id}/?apikey={$this->apikey}";
		return $this->jsoncacheable->get_json( $this->format_url( $url ) );
	}

	/**
	 * Calls the District Search
	 */
	public function get_district_search( $state, $chamber ) {
		$url = OSAPI_URL_DISTRICTSEARCH . "/{$state}/{$chamber}/?apikey={$this->apikey}";
		return $this->jsoncacheable->get_json( $this->format_url( $url ) );
	}

	/**
	 * Calls the District Boundary Lookup
	 */
	public function get_district_boundary_lookup( $boundary_id ) {
		$url = OSAPI_URL_DISTRICTBOUNDARYLOOKUP . "/{$boundary_id}/?apikey={$this->apikey}";
		return $this->jsoncacheable->get_json( $this->format_url( $url ) );
	}

	/** PRIVATE FUNCTIONS BELOW **/

	private function format_url( $url ) {
		return str_replace( ' ', '%20', $url ); // API likes %20 as spaces
	}

	/**
	 * Returns $fields as a comma-delimited list to append to the end of a URL
	 */
	private function add_fields( $fields ) {
		if( is_array( $fields ) ) {
			return '&fields=' . implode( ',', $fields );
		}
	}

	/**
	 * Returns $search_parameters as a GET list to append to the end of a URL
	 */
	private function add_search_parameters( $search_parameters ) {
		if( is_array( $search_parameters ) ) {
			$result = '';
			foreach( $search_parameters as $key => $value ) {
				$result .= "&{$key}={$value}";
			}
			return $result;
		}
	}

	/**
	 * Formats the bill_id.
	 */
	private function format_bill_id( $bill_id ) {
		if( strpos( $bill_id, ' ' ) == false ) {
			// Put space between HB/SB and # if it doesn't exist
			$bill_parts = preg_split( '/(?=[0-9])/', $bill_id, $flags=PREG_SPLIT_DELIM_CAPTURE );
			$bill_id = $bill_parts[0] . ' ' . $bill_parts[1];
		}
		return $bill_id;
	}
}
