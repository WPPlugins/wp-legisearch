<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function legisearch_settings_page() { 
	require_once( LEGISEARCH__PLUGIN_DIR . '/admin/settings.page.php' );
}

function legisearch_bill_page() {
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
	if( ! LegisearchSettings::getInstance()->is_osapikey_valid() ) {
		wp_die( "You must set a valid Open States API Key in <a href=\"?page=legisearch-settings\">Settings</a> for these pages to work." );
	}
	if( !isset( $_GET['action'] ) ) {
		require_once( LEGISEARCH__PLUGIN_DIR . '/admin/allbills.page.php' );
	}
	elseif( $_GET['action'] == 'edit' ) {
		require_once( LEGISEARCH__PLUGIN_DIR . '/admin/editbill.page.php' );
	}
	elseif( $_GET['action'] == 'remove' ) {
		legisearch_remove_bill( $_GET['bill'] );
	}
}

function legisearch_add_bill_page() { 
	if( ! LegisearchSettings::getInstance()->is_osapikey_valid() ) {
		wp_die( "You must set a valid Sunlight Foundation API Key in <a href=\"?page=legisearch-settings\">Settings</a> for these pages to work." );
	}
	require_once( LEGISEARCH__PLUGIN_DIR . '/admin/addbill.page.php' );
}

/**
 * Show error or update message
 */
function legisearch_show_messages( ) {
	global $legisearch_update_msg, $legisearch_error_msg;
	if( isset( $legisearch_error_msg ) ) : ?>
		<div id="message" class="error"><?php echo $legisearch_error_msg; ?></div>
	<?php elseif( isset( $legisearch_update_msg ) ) : ?>
		<div id="message" class="updated"><?php echo $legisearch_update_msg; ?></div>
	<?php endif;
}

function legisearch_get_trackedbill_by_id( $os_id ) {
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Bill.class.php' );
	global $wpdb;
	$sql = "SELECT state, session, bill_id FROM " . LEGISEARCH__BILLSTRACKED_TABLENAME . " WHERE id='{$os_id}';";
	$row = $wpdb->get_row( $sql, OBJECT );
	if( $wpdb->num_rows == 1 ) {
		return new Bill( $row->state, $row->session, $row->bill_id );
	}
	else {
		return false;
	}
}

function legisearch_get_all_tracked_bills( ) {
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

function legisearch_remove_bill( $id ) {
	global $legisearch_update_msg, $wpdb;
	if( isset( $id ) ) {
		$wpdb->delete( LEGISEARCH__BILLSTRACKED_TABLENAME, array( 'id' => $id ) );
		if( $wpdb->num_rows == 1 ) {
			$legisearch_update_msg = "Bill Removed";
		}
		else {
			$legisearch_error_msg = "There was a database error";
		}
	}
	else {
		$legisearch_error_msg = "There was an error";
	}
	require_once( LEGISEARCH__PLUGIN_DIR . '/admin/allbills.page.php' );
}
