<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( LEGISEARCH__PLUGIN_DIR . 'os-client/JsonCacheable.class.php' );

/**
 * A implementation of JsonCacheable that can be used in Wordpress plugins
 * Expects table to be ( url=>string, data=>string, $retrieved->datetime )
 */
class WpJsonCacheable extends JsonCacheable {

	private $cacheTimeout = null;
	private $cacheTableName = null;

	/**
	 * Constructor
	 */
	public function __construct( $cache_timeout, $cache_table_name ) {
		$this->cacheTimeout = $cache_timeout;
		$this->cacheTableName = $cache_table_name;
	}

	/**
	 * Gets json from $url, or retrieves from $this->cacheTableName if cache exists and its fresh.
	 */
	public function get_json( $url ) {
		global $wpdb;

		// First, let's look at the cache
		$sql = "SELECT * FROM {$this->cacheTableName} WHERE url = '{$url}';";
		$result = $wpdb->get_row( $sql );

		if( $wpdb->num_rows > 0 && ( strtotime( $result->retrieved ) >= strtotime( "- {$this->cacheTimeout} hours" ) ) ) {
			// Cache match and it's fresh
			return $result->data;
		}
		else if ( $wpdb->num_rows > 0 ){
			// Not fresh, replace the row and return the json
			$api_response = wp_remote_get( $url );
			$json = wp_remote_retrieve_body( $api_response );
			if( empty( $json ) || !json_encode( $json ) ) {
				return false; // Don't cache bad result
			}
			$wpdb->update(
				$this->cacheTableName,
				array(		// data
					'url' => $url,
					'data' => $json,
					'retrieved' => current_time( 'mysql' )
				),
				array(		// where
					'url' => $url
				),
				array(		// data format
					'%s',
					'%s',
					'%s'
				),
				array(		// where format
					'%s'
				)
			);
			
			return $json;
		}
		else {
			// Doesn't exist
			$api_response = wp_remote_get( $url );
			$json = wp_remote_retrieve_body( $api_response );
			$wpdb->replace(
				$this->cacheTableName,
				array(		// data
					'url' => $url,
					'data' => $json,
					'retrieved' => current_time( 'mysql' )
				),
				array(		// data format
					'%s',
					'%s',
					'%s'
				)
			);
			return $json;
		}
	}

	/**
	 * Removes all rows from cache table
	 */
	public function remove_cache( ) {
		global $wpdb;
		return $wpdb->query( "TRUNCATE TABLE {$this->cacheTableName}"  );
	}
}

