<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function legisearch_filter_the_content( $content ) {
	if ( !in_the_loop() ) {	// Only filter title on top
		return $content;
	}
	$state = get_query_var( 'state', null );
	$chamber_a = get_query_var( 'chamber_a', null );
	$district_a = get_query_var( 'district_a', null );
	$chamber_b = get_query_var( 'chamber_b', null );
	$district_b = get_query_var( 'district_b', null );
	$chamber_c = get_query_var( 'chamber_c', null );
	$district_c = get_query_var( 'district_c', null );
	$leg_id = get_query_var( 'leg_id', null );

	// Use legisearch css if avaiable.
	if( file_exists( TEMPLATEPATH . '/legisearch.css' ) ) { // Use template css file if available
		wp_enqueue_style( 'georesult-page-css', get_stylesheet_directory_uri() . '/legisearch.css' );
	}

	if( !empty( $state ) && !empty( $chamber_a) && !empty( $district_a ) && !empty( $chamber_b ) && !empty( $district_b ) ) { // GeoResult Page
		require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/StateMetaData.class.php' );
		$state_meta = new StateMetaData( $state );
		require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Legislator.class.php' );
		$legislators_a = Legislator::get_legislators_by_district( $state, $chamber_a, $district_a );
		$legislators_b = Legislator::get_legislators_by_district( $state, $chamber_b, $district_b );
		$legislators_c = array();// Special case for DC
		if ( !empty( $district_c ) && !empty( $chamber_c ) ) { 
			$legislators_c = Legislator::get_legislators_by_district( $state, $chamber_b, $district_c );
		}
		$valid = true;
		foreach( $legislators_a as $leg ) {
			$valid = $leg->is_osvalid();
		}
		foreach( $legislators_b as $leg ) {
			$valid = $leg->is_osvalid();
		}
		foreach( $legislators_c as $leg ) {
			$valid = $leg->is_osvalid();
		}
		if( $valid ) {
			wp_register_script( 
				'georesult.page.js', 
				LEGISEARCH__PLUGIN_URL . '/public/georesult.page.js',
				'jQuery');
			wp_localize_script( 'georesult.page.js', 'ajaxurl', admin_url('admin-ajax.php') );
			wp_enqueue_script( 'georesult.page.js' );
			$legislators = array_merge( $legislators_a, $legislators_b, $legislators_c );

			if( !file_exists( TEMPLATEPATH . '/legisearch.css' ) ) { // Use default css if needed
				wp_enqueue_style( 'georesult-page-css', LEGISEARCH__PLUGIN_URL . '/public/georesult.page.css' );
			}

			if( function_exists( 'legisearch_override_write_the_georesult' ) ) {
				// First, check to see if user has overridden the default georesult page
				return legisearch_override_write_the_georesult( $legislators, $state_meta );
			}
			else {
				// Default georesult page.
				require_once( LEGISEARCH__PLUGIN_DIR . '/public/georesult.page.php' );
				return write_the_georesult( $legislators, $state_meta );
			}
		}	
	}
	else if ( !empty( $state ) && !empty( $leg_id ) ) {	// Incumbent Page 
		require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Legislator.class.php' );
		$legislator = new Legislator( $leg_id );
		if( $legislator->is_osvalid() ) {
			$votes = $legislator->get_all_tracked_votes();
			if( !file_exists( TEMPLATEPATH . '/legisearch.css' ) ) { // Use default css if needed
				wp_enqueue_style( 'georesult-page-css', LEGISEARCH__PLUGIN_URL . '/public/incumbent.page.css' );
			}

			if( function_exists( 'legisearch_override_write_incumbent_part' ) ) {
				// First, check to see if user has overridden the default incumbent page
				return legisearch_override_write_incumbent_part( $legislator, $votes );
			}
			else {
				// Default incumbent page.
				require_once( LEGISEARCH__PLUGIN_DIR . '/public/incumbent.page.php' );
				return write_incumbent_part( $legislator, $votes );
			}
		}
		else {
			return "No results found.";
		}
	}
	else if ( !empty( $state ) && !empty( $chamber_a) ) {	// Chamber Page
		require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/StateMetaData.class.php' );
		$state_meta = new StateMetaData( $state );
		require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/ChamberMembers.class.php' );
		$chamber_members = new ChamberMembers( $state, $chamber_a );

		if( !file_exists( TEMPLATEPATH . '/legisearch.css' ) ) { // Use default css if needed
			wp_enqueue_style( 'georesult-page-css', LEGISEARCH__PLUGIN_URL . '/public/chamber.page.css' );
		}

		if( function_exists( 'legisearch_override_write_chamber_part' ) ) {
				// First, check to see if user has overridden the default chamber page
				return legisearch_override_write_chamber_part( $chamber_members, $state_meta );
		}
		else {
			// Default chamber page.
			require_once( LEGISEARCH__PLUGIN_DIR . '/public/chamber.page.php' );
			return write_chamber_part( $chamber_members, $state_meta );
		}
	}
	else {
		return $content;
	}
}

function legisearch_get_all_tracked_bills( ) {
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Bill.class.php' );
	global $wpdb;

	$tracked_bills = array();
	$sql = "SELECT * FROM " . LEGISEARCH__BILLSTRACKED_TABLENAME . " WHERE 1;";
	$result = $wpdb->get_results( $sql, OBJECT_K );
	krsort( $result ); // Reverse sort by keys (should be newest first)
	foreach( $result as $row ) {
		$tracked_bills[] = new Bill( $row->state, $row->session, $row->bill_id );
	}
	return $tracked_bills;
}

function legisearch_search_by_address_shortcode( ) {
	$html = '<div class="legisearch-geo-lookup">';
	$html .= '<form method="post" class="legisearch-geo-lookup-form" action="" />';
	$html .= '<div class="legisearch-geo-address">';
	$html .= '<label class="legisearch-geo-address-label" for="address">Address</label>';
	$html .= '<input class="legisearch-geo-address-input" type="text" name="address" placeholder="i.e. 123 Main Street, Birmingham AL" />';
	$html .= '</div>';
	$html .= '<div class="legisearch-geo-submit">';
	$html .= '<input type="hidden" name="legisearch_base_url" value="' . get_permalink() . '" />';
	$html .= '<input class="legisearch-geo-submit-button" name="geo-submit" type="submit" value="Search" />';
	$html .= wp_nonce_field( 'legisearch_geosearch','legisearch_geosearch_security' );
	$html .= '</div>';
	$html .= '</div>';
	$html .= '</form>';
	return $html;
}

function legisearch_chamberlink_shortcode( $attr ) {
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/StateMetaData.class.php' );
	// Get state and chamber
	$state = strtolower( $attr['state'] );
	$chamber = strtolower( $attr['chamber'] );
	if( !in_array( $state, array ( 'al', 'ak', 'az', 'ar', 'ca', 'co', 'ct', 'de', 'dc', 'fl', 'ga', 'hi', 'id', 'il', 'in', 'ia', 'ks', 'ky', 'la', 'me', 'md', 'ma', 'mi', 'mn', 'ms', 'mo', 'mt', 'ne', 'nv', 'nh', 'nj', 'nm', 'ny', 'nc', 'nd', 'oh', 'ok', 'or', 'pa', 'ri', 'sc', 'sd', 'tn', 'tx', 'ut', 'vt', 'va', 'wa', 'wv', 'wi', 'wy' ) ) ) {
		// Not a valid state, return nothing
		return '';
	}
	if( !in_array( $chamber, array( 'upper', 'lower' ) ) ) {
		// Not a valid chamber, return nothing
		return '';
	}
	$url = get_permalink() . "/{$state}/{$chamber}";
	$state_meta = new StateMetaData( $state );
	$text = $state_meta->name . ' ' . $state_meta->chambers->$chamber->name;
	return "<a class=\"legisearch-chamber-link legisearch-{$state}-{$chamber}-link\" href=\"{$url}\">{$text}</a>";
}
	

require_once( 'geolookup.config.php' );
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Legislator.class.php' );

function legisearch_geo_redirect( ) {
	if ( !check_admin_referer( 'legisearch_geosearch','legisearch_geosearch_security' ) ) {
		wp_die( 'Security issue detected' );	
	}
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
	$settings = LegisearchSettings::getInstance();

	$address = $_POST['address'];
	$base_url = $_POST['legisearch_base_url'];
	
	$address_encoded = urlencode( $address );
	$url = GOOGLEMAPSAPI_BASE_URL . $address_encoded . "&key={$settings->gm_serverkey}";

	$resp_http = wp_remote_get( $url );
	$resp_json = wp_remote_retrieve_body( $resp_http );
	$resp = json_decode( $resp_json, true );
	if( $resp['status'] == 'OK' ) {
		$lat = $resp['results'][0]['geometry']['location']['lat'];
        	$lon = $resp['results'][0]['geometry']['location']['lng'];
		$state_code = legisearch_get_state_code( $resp );
		if( $state_code ) {
			$district_info = Legislator::get_disticts_by_geo( $lat, $lon );
			$args = array($state_code);
			foreach( $district_info as $district ) {
				if( !in_array( "/{$district->chamber}/{$district->district}", $args ) ) {
					$args[] = "/{$district->chamber}/{$district->district}";
				}
			}
			$url =  $base_url . '/' . implode( '/', $args );
			$url = str_replace( ' ', '%20', $url );

			wp_redirect( $url, 302 );
			exit;
		}
	}
}

function legisearch_get_state_code( $response ) {
	foreach( $response['results'][0]['address_components'] as $comp ) {
		if( in_array( 'administrative_area_level_1', $comp['types'] ) ) {
			return strtolower( $comp['short_name'] );
		}
	}
	return false;
}
