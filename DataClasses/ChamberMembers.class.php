<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . '/os-client/WpJsonCacheable.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . '/os-client/OsClient.class.php' );

class ChamberMembers implements iOsData {

	private $_os_client = null;
	private $_os_data = null;
	private $_os_boundary_data = null;

	public function __construct( $state, $chamber ) {
		// Get plugin settings
		$legisearch_settings = LegisearchSettings::getInstance();
		$apikey = $legisearch_settings->os_api_key;
		$cache_timeout = $legisearch_settings->os_cache_timeout;
		$cache_tablename = LEGISEARCH__OSCACHE_TABLENAME;

		// Dependency Injection
		$json_cacheable = new WpJsonCacheable( $cache_timeout, $cache_tablename );
		$this->_os_client = new OsClient( $legisearch_settings->os_apikey, $json_cacheable );
		$search_parameters = array(
			'state' => $state,
			'active' => 'true',
			'chamber' => $chamber);
		$json_return = json_decode( $this->_os_client->get_legislator_search( $search_parameters ) );
		if( $json_return ) {
			$this->_os_data = $json_return;
			uasort( $this->_os_data, array( $this, 'district_sort' ) );
		}
	}

	public function __get( $name ) {
		if( $name == 'members' ) {
			return $this->_os_data;
		}
	}

	public function __set( $name, $value ) {
		// Read only Data
	}

	public function is_osvalid() {
		return !empty( $this->_os_data );
	}

	private function district_sort( $a, $b ) {
		$district_a = $a->district;
		$district_b = $b->district;
		if( is_numeric( $district_a ) && is_numeric( $district_b ) ) {
			return $district_a - $district_b;
		}
		else {
			return strcasecmp( $district_a, $district_b );
		}
	}
}
