<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
abstract class JsonCacheable {

	/**
	 * Retrieves the json from the given $url or uses cache 
	 */
	abstract public function get_json( $url );

	/**
	 * Removes all cache
	 */
	abstract public function remove_cache( );

}
