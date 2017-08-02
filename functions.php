<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . '/os-client/WpJsonCacheable.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . '/os-client/OsClient.class.php' );

function legisearch_getGoogleMapSettings() {
	$state = $_POST['state'];
	$chamber_a = $_POST['chamber_a'];
	$district_a = $_POST['district_a'];
	$chamber_b = $_POST['chamber_b'];
	$district_b = $_POST['district_b'];
	if( isset( $_POST['chamber_c'] ) && isset( $_POST['district_c'] ) ) {
		$chamber_c = $_POST['chamber_c'];
		$district_c = $_POST['district_c'];
	}

	// Set up result object
	$result = new stdClass();

	// Get Google Maps Browser Key
	$gmkey = LegisearchSettings::getInstance()->gm_browserkey;

	// Set URL
	$result->url = "https://maps.googleapis.com/maps/api/js?key={$gmkey}&callback=initMap";

	// Get boundary id of the districts. Ignore 'At-Large' Districts.
	$result->chambers = array();
	if( $district_a != 'At-Large' && $district_a != 'Chairman' ) {
		$data = array();
		$boundary_data = legisearch_get_boundaries( $state, $chamber_a, $district_a );
		$data['boundaries']  = $boundary_data->polygons;
		$data['centerlat'] = $boundary_data->centerlat;
		$data['centerlon'] = $boundary_data->centerlon;
		$data['color'] = ( $chamber_a == 'upper' ) ? '#0000FF' : '#FF0000';
		$result->chambers[] = $data;
	}
	if( $district_b != 'At-Large' && $district_b != 'Chairman' ) {
		$data = array();
		$boundary_data = legisearch_get_boundaries( $state, $chamber_b, $district_b );
		$data['boundaries']  = $boundary_data->polygons;
		$data['centerlat'] = $boundary_data->centerlat;
		$data['centerlon'] = $boundary_data->centerlon;
		$data['color'] = ( $chamber_b == 'upper' ) ? '#0000FF' : '#FF0000';
		$result->chambers[] = $data;
	}
	if( isset( $district_c ) && $district_c != 'At-Large' && $district_c != 'Chairman' ) {
		$data = array();
		$boundary_data = legisearch_get_boundaries( $state, $chamber_c, $district_c );
		$data['boundaries']  = $boundary_data->polygons;
		$data['centerlat'] = $boundary_data->centerlat;
		$data['centerlon'] = $boundary_data->centerlon;
		$data['color'] = ( $chamber_c == 'upper' ) ? '#0000FF' : '#FF0000';
		$result->chambers[] = $data;
	}

	echo json_encode( $result );
	die();
}

function legisearch_get_boundaries( $state, $chamber, $district ) {
	$result = new stdClass();
	$settings = LegisearchSettings::getInstance();
	$cacheable = new WpJsonCacheable( $settings->os_cache_timeout, LEGISEARCH__OSCACHE_TABLENAME );
	$osclient = new OsClient( $settings->os_apikey, $cacheable );
	$district_data = json_decode( $osclient->get_district_search( $state, $chamber ) );
	$district_str = str_replace( '%20', ' ', $district );
	
	$boundary_id = null;
	foreach( $district_data as $item ) {
		if( $item->name == $district_str ) {
			$boundary_id = $item->boundary_id;
			break;
		}
	}
	if( $boundary_id ) {
		$json = json_decode( $osclient->get_district_boundary_lookup( $boundary_id ) );
		$result->polygons = $json->shape;
		$result->centerlat = $json->region->center_lat;
		$result->centerlon = $json->region->center_lon;
	}
	return $result;
}
