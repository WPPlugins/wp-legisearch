<?php 
/*
Plugin Name: WP Legisearch
Plugin URI: http://droberts.us/legisearch
Description: A state legislative tracking system for activist organizations that uses the Open States API. 
Version: 1.3.1
Author: Dan Roberts
Author URI: http://droberts.us
License: GPLv2
*/

/*
Copyright 2015 Dan Roberts (email:wp@droberts.us)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// Define macros
global $wpdb;
define( 'LEGISEARCH_VERSION', '1.3.1' );
define( 'LEGISEARCH__MINIMUM_WP_VERSION', '4.3' );
define( 'LEGISEARCH__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEGISEARCH__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEGISEARCH__THEME_DIR', get_template_directory( ) );
define( 'LEGISEARCH__BILLSTRACKED_TABLENAME', $wpdb->prefix . "legisearch_billstracked" );
define( 'LEGISEARCH__VOTESTRACKED_TABLENAME', $wpdb->prefix . "legisearch_votestracked" );
define( 'LEGISEARCH__OSCACHE_TABLENAME', $wpdb->prefix . "legisearch_os_cache" );
define( 'LEGISEARCH__BADAPI_MESSAGE', 'Sunlight API key is either missing or invalid. Visit the <a href="' . admin_url('admin.php?page=legisearch-settings') . '">Legisearch Settings</a> page.' );

// Register activate and deactivate hooks
register_activation_hook( __FILE__, 'legisearch_activation' );
register_deactivation_hook( __FILE__, 'legisearch_deactivate' );

require_once( LEGISEARCH__PLUGIN_DIR . 'functions.php' );

if( is_admin() ) {
	// Admin pages
	add_action( 'wp_ajax_legisearch_get_sessions', 'legisearch_getSessions' );
	add_action( 'wp_ajax_legisearch_get_bill', 'legisearch_getBill' );
	add_action( 'admin_menu', 'legisearch_create_menu' );
	require_once( LEGISEARCH__PLUGIN_DIR . '/admin/admin.ajax.php' );
	require_once( LEGISEARCH__PLUGIN_DIR . '/admin/functions.php' ); 
}
else {
	if( isset( $_POST['geo-submit'] ) ) {
		add_action( 'init', 'legisearch_geo_redirect' );
	}
	// Public functionality
	add_filter( 'the_content', 'legisearch_filter_the_content' );
	require_once( LEGISEARCH__PLUGIN_DIR . '/public/functions.php' );
	// Register shortcodes
	add_shortcode( 'legisearch_addresslookup', 'legisearch_search_by_address_shortcode' );
	add_shortcode( 'legisearch_chamberlink', 'legisearch_chamberlink_shortcode' );
}

// Register other hooks and filters
add_action( 'init', 'legisearch_update_db_check' );
add_action( 'init', 'legisearch_add_rewrite_rules' );
add_filter( 'query_vars', 'legisearch_query_vars' );
add_action( 'wp_ajax_nopriv_get_gmap_settings', 'legisearch_getGoogleMapSettings' );
add_action( 'wp_ajax_get_gmap_settings', 'legisearch_getGoogleMapSettings' );

/**
 * Activate plugin
 */
function legisearch_activation() {
	// Require Wordpress 4.3 or higher
	if ( version_compare( get_bloginfo( 'version' ), LEGISEARCH__MINIMUM_WP_VERSION, '<' ) ) {
		deactivate_plugins( basename( __FILE__ ) );	// Deactivate Legisearch
	}
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// Default settings
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
	$legisearch_settings = LegisearchSettings::getInstance();
	$legisearch_settings->os_cache_timeout = 96;

	// Create Plugin Tables
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE " . LEGISEARCH__BILLSTRACKED_TABLENAME . " (
		id varchar(25) NOT NULL,
		state varchar(2) NOT NULL,
		session varchar(50) NOT NULL,
		bill_id varchar(10) NOT NULL,
		description TEXT NULL,
		PRIMARY KEY  (id)
		) {$charset_collate};";
	dbDelta( $sql );

	$sql = "CREATE TABLE " . LEGISEARCH__VOTESTRACKED_TABLENAME . " (
		id VARCHAR(11) NOT NULL,
		bill_os_id VARCHAR(25) NOT NULL,
		description TEXT NULL,
		track BOOLEAN NOT NULL,
		vote_pref_type ENUM('neutral','prefer') NULL,
		vote_pref CHAR NULL,
		PRIMARY KEY  (id)
		) {$charset_collate};";
	dbDelta( $sql );

	$sql = "CREATE TABLE " . LEGISEARCH__OSCACHE_TABLENAME . " (
		id INT NOT NULL AUTO_INCREMENT,
		url VARCHAR(300) NOT NULL,
		data LONGTEXT NOT NULL,
		retrieved TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id)
		) {$charset_collate};";
	dbDelta( $sql );

	// Rewrite rules
	legisearch_add_rewrite_rules();
	flush_rewrite_rules();
}

function legisearch_update_db_check() {
	if( get_site_option( 'legisearch_db_option' ) != '1.3' ) {
		legisearch_activation();
		update_site_option( 'legisearch_db_option', '1.3' );
	}
}

/**
 * Deactivate plugin
 */
function legisearch_deactivate() {
	flush_rewrite_rules();
}

/**
 * Add a custom pretty url structure /$state[/$chamber_a][/$district_a][/$chamber_b/$district_b]
 */
function legisearch_add_rewrite_rules() {
	// incumbent match
	add_rewrite_rule( '(.*)/?(al|ak|az|ar|ca|co|ct|de|dc|fl|ga|hi|id|il|in|ia|ks|ky|la|me|md|ma|mi|mn|ms|mo|mt|ne|nv|nh|nj|nm|ny|nc|nd|oh|ok|or|pa|ri|sc|sd|tn|tx|ut|vt|va|wa|wv|wi|wy)/legislators/?([a-zA-Z0-9]+)?',
	'index.php?pagename=$matches[1]&state=$matches[2]&leg_id=$matches[3]',
	'top' );
	// chamber/georesult match
	add_rewrite_rule( '(.*)/?(al|ak|az|ar|ca|co|ct|de|dc|fl|ga|hi|id|il|in|ia|ks|ky|la|me|md|ma|mi|mn|ms|mo|mt|ne|nv|nh|nj|nm|ny|nc|nd|oh|ok|or|pa|ri|sc|sd|tn|tx|ut|vt|va|wa|wv|wi|wy)/(upper|lower)?/?([%0-9a-zA-Z-]+)?/?(upper|lower)?/?([%0-9a-zA-Z-]+)?/?(upper|lower)?/?([%0-9a-zA-Z-]+)?',
	'index.php?pagename=$matches[1]&state=$matches[2]&chamber_a=$matches[3]&district_a=$matches[4]&chamber_b=$matches[5]&district_b=$matches[6]&chamber_c=$matches[7]&distric_c=$matches[8]', 'top' );
}

/**
 * Add custom query vars
 */
function legisearch_query_vars( $vars ) {
	$vars[] = 'state';
	$vars[] = 'chamber_a';
	$vars[] = 'district_a';
	$vars[] = 'chamber_b';
	$vars[] = 'district_b';
	$vars[] = 'leg_id';
	return $vars;
}

/**
 * Admin Menu
 */
function legisearch_create_menu() {
	// Create top-level menu
	add_menu_page( 'Legisearch Settings', 'Legisearch', 'manage_options', 'legisearch-bill', 
		'legisearch_bill_page', LEGISEARCH__PLUGIN_URL . '/images/wp-icon-16.png', 25.1 );
	// Create submenus
	add_submenu_page( 'legisearch-bill', 'All Bills', 'All Bills', 'manage_options', 'legisearch-bill',
		'legisearch_bill_page' );
	add_submenu_page( 'legisearch-bill', 'Add New Bill', 'Add Bill', 'manage_options', 'legisearch-add-bill',
		'legisearch_add_bill_page' );
	add_submenu_page( 'legisearch-bill', 'Legisearch Settings', 'Settings', 'manage_options', 'legisearch-settings', 
		'legisearch_settings_page' );	
}

