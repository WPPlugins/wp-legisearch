<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . "/os-client/OsClient.class.php" );
require_once( LEGISEARCH__PLUGIN_DIR . "/os-client/WpJsonCacheable.class.php" );
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/iOsData.interface.php' );

class StateMetaData implements iOsData {

	private $_os_data = null;

	public function __construct( $state ) {
		// Get plugin settings
		$legisearch_settings = LegisearchSettings::getInstance();
		$apikey = $legisearch_settings->os_api_key;
		$cache_timeout = $legisearch_settings->os_cache_timeout;
		$cache_tablename = LEGISEARCH__OSCACHE_TABLENAME;

		// Dependency Injection
		$json_cacheable = new WpJsonCacheable( $cache_timeout, $cache_tablename );
		$os_client = new OsClient( $legisearch_settings->os_apikey, $json_cacheable );
		$json_return = json_decode( $os_client->get_state_metadata( $state ) );
		if( $json_return ) {
			$this->_os_data = $json_return;
		}
	}

	public function __get( $name ) {
		try {
			return $this->_os_data->$name;
		}
		catch(Exception $e) {
			return false;
		}
	}

	public function __set( $name, $value ) {
		// Read only Data
	}

	public function is_osvalid() {
		return !empty( $this->_os_data );
	}

}
