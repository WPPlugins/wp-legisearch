<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'wp_ajax_legisearch_get_sessions', 'legisearch_get_sessions_nonce_check' );
function legisearch_get_sessions_nonce_check() {
	check_ajax_referer( 'legisearch_addbill', 'security' );
	die;
}
add_action( 'wp_ajax_legisearch_get_bill', 'legisearch_get_bill_nonce_check' );
function legisearch_get_bill_nonce_check() {
	check_ajax_referer( 'legisearch_addbill', 'security' );
	die;
}

/**
 * Return a reverse-sorted list of session_details given a state.
 */
function legisearch_getSessions( ) {
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/StateMetaData.class.php' );

	$state = sanitize_text_field ( $_POST['state'] );
	$state_meta = new StateMetaData( $state );
	$session_details_json = json_encode( $state_meta->session_details );
	$session_details = json_decode( $session_details_json, true ); // Get it into an array so we can sort
	uasort( $session_details, 'legisearch_session_sort_reverse' ); // Sort
	echo json_encode( $session_details ); // Return json of sorted session details

	die();
}

/**
 * Sort an associative array of session_details in reverse order. The goal here is to reverse-sort
 * chronologically. Many states may need special treatment for this to fully work.
 */
function legisearch_session_sort_reverse( $a, $b ) {
	if( array_key_exists( 'internal_id', $a ) ) {
		// Sort by interal key if available
		$first = intval( $a['internal_id'] );
		$second = intval( $b['internal_id'] );
		return $second - $first;
	}
	else if( array_key_exists( 'session_id', $a ) ) {
		// Sort by session id if exists
		$first = intval( $a['session_id'] );
		$second = intval( $b['session_id'] );
		return $second - $first;
	}
	else if( array_key_exists( 'display_name', $a ) ) {
		return -1*strcmp( $a['display_name'], $b['display_name'] );
	}
	else {
		return -1;
	}
}

/**
 * Return bill data
 */
function legisearch_getBill() {
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Bill.class.php' );

	$state = sanitize_text_field ( $_POST['state'] );
	$session = sanitize_text_field ( $_POST['session'] );
	$bill_id = sanitize_text_field ( $_POST['bill_id'] );

	$bill = new Bill( $state, $session, $bill_id );

	// Because we use tricky properties, we can't just encode the jason of the object.
	$jsonable_bill = array();
	$jsonable_bill['bill_id'] = $bill->bill_id;
	$jsonable_bill['title'] = $bill->title;
	$jsonable_bill['sponsors'] = $bill->sponsors;
	$jsonable_bill['subjects'] = $bill->subjects;
	$jsonable_bill['session'] = $bill->session;	
	$jsonable_bill['state'] = $bill->state;
	$jsonable_bill['id'] = $bill->id;
	$jsonable_bill['description'] = '';
	if( $jsonable_bill['description'] != null ) {
		$jsonable_bill['description'] = $bill->description;
	}

	echo json_encode( $jsonable_bill );

	die();

}
