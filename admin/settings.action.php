<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if( isset( $_POST['save_settings'] ) ) {
	// Security. This method has the ability to alter WP options
	if ( !check_admin_referer( 'legisearch_change_settings','legisearch_action' ) ) {
		wp_die( 'Security issue detected' );	
	}
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions' );	
	}

	global $legisearch_update_msg, $legisearch_error_msg;
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
	$legisearch_settings = LegisearchSettings::getInstance();

	// Options
	$os_api_key = trim( $_POST['legisearch_os_apikey'] );
	$gm_server_key = trim( $_POST['legisearch_gm_serverkey'] );
	$gm_browser_key = trim( $_POST['legisearch_gm_browserkey'] );
	$os_cache_timeout = trim( $_POST['legisearch_os_cachetimeout'] );

	$legisearch_settings->os_apikey = $os_api_key;
	$legisearch_settings->gm_serverkey = $gm_server_key;
	$legisearch_settings->gm_browserkey = $gm_browser_key;

	// Test if it worked
	$legisearch_error_msg = null;
	if( $legisearch_settings->os_apikey != $os_api_key ) {
		$legisearch_error_msg .= "Invalid Openstates API key. ";
	}
	if( $legisearch_settings->gm_browserkey != $gm_browser_key ) {
		$legisearch_error_msg .= "Invalid Google Maps Browser key. ";
	}
	if( $legisearch_settings->gm_serverkey != $gm_server_key ) {
		$legisearch_error_msg .= "Invalid Google Maps Server key. ";
	}
	if( $legisearch_error_msg == '' ) {
		$legisearch_update_msg .= "Settings Saved. ";
	}

	// Sunlight Foundation API cache timeout
	$os_cache_timeout = intval( trim( $_POST['legisearch_os_cachetimeout'] ) );
	// validate
	if( !is_numeric( $os_cache_timeout) || $os_cache_timeout <= 0 ) {
		// Has to be something that can be converted to a integer
		$legisearch_error_msg = "Cache timeout must be an integer greater than 0";
	}
	else {
		// save
		update_option( 'legisearch_openstates_cachetimeout', $os_cache_timeout );
		$legisearch_update_msg = "Settings saved";
	} 
}

if( isset( $_POST['remove_cache'] ) ) {
	// Security. This method has the ability to alter the database
	if ( !check_admin_referer( 'legisearch_change_settings','legisearch_action' ) ) {
		wp_die( 'Security issue detected' );	
	}
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions' );	
	}

	global $legisearch_update_msg, $legisearch_error_msg;
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
	$settings = LegisearchSettings::getInstance();
	require_once( LEGISEARCH__PLUGIN_DIR . '/os-client/WpJsonCacheable.class.php' );

	$jsonCacheable = new WpJsonCacheable( $settings->os_cache_timeout, LEGISEARCH__OSCACHE_TABLENAME );

	if( $jsonCacheable->remove_cache() ) {
		$legisearch_update_msg = "Cache Cleared";
	}
	else {
		$legisearch_error_msg = "There was a database error clearing the cache";
	}
}
