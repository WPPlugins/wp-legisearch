<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Legisearch Plugin settings class.
 * Depends on being instantiated within the wordpress environment
 */
class LegisearchSettings {

	const OS_APIKEY_OPTION_NAME = 'legisearch_openstates_apikey';
	const OS_CACHETIMEOUT_OPTION_NAME = 'legisearch_openstates_cachetimeout';
	const GM_BROWSERKEY_OPTION_NAME = 'legisearch_googlemaps_browserkey';
	const GM_SERVERKEY_OPTION_NAME = 'legisearch_googlemaps_serverkey';

	private static $instance;
	
	public static function getInstance() {
		if( null == static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Singleton pattern
	 */ 
	protected function __construct( ) { }
	private function __clone( ) { }
	private function __wakeup( ) { }

	public function __set( $name, $value ) {
		switch( $name ) {
			case 'os_apikey':
				update_option( self::OS_APIKEY_OPTION_NAME, $value );
				break;
			case 'os_cache_timeout':
				return update_option( self::OS_CACHETIMEOUT_OPTION_NAME, $value );
				break;
			case 'gm_browserkey':
				return update_option( self::GM_BROWSERKEY_OPTION_NAME, $value );
				break;
			case 'gm_serverkey':
				return update_option( self::GM_SERVERKEY_OPTION_NAME, $value );
				break;
			default:
				break;
		}
	}	

	public function __get( $name ) {
		switch( $name ) {
			case 'os_apikey':
				return get_option( self::OS_APIKEY_OPTION_NAME );
				break;
			case 'os_cache_timeout':
				return get_option( self::OS_CACHETIMEOUT_OPTION_NAME );
				break;
			case 'gm_browserkey':
				return get_option( self::GM_BROWSERKEY_OPTION_NAME );
				break;
			case 'gm_serverkey':
				return get_option( self::GM_SERVERKEY_OPTION_NAME );
				break;
			default:
				break;
		}
	}

	public function is_osapikey_valid() {
		$apikey = $this->os_apikey;
		require_once( LEGISEARCH__PLUGIN_DIR . '/os-client/OsClient.class.php' );
		if( strlen( $apikey ) != 32 ) {
			return false;
		}
		else {
			return true;
		}
	}

}
