<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $legisearch_update_msg, $legisearch_error_msg;

if( isset( $_POST['legisearch_searchedbill_addbutton'] ) ) {
	// Security. This method has the ability to alter the database.
	if ( !check_admin_referer( 'legisearch_add_tracked_bill','legisearch_action' ) ) {
		wp_die( 'Security issue detected' );	
	}
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions' );	
	}

	global $wpdb;
	$id = sanitize_text_field( $_POST['legisearch_searchedbill_os_id'] );
	$state = sanitize_text_field( $_POST['legisearch_searchedbill_state'] );
	$session = sanitize_text_field( $_POST['legisearch_searchedbill_session'] );
	$bill_id = sanitize_text_field( $_POST['legisearch_searchedbill_bill_id'] );
	$description = sanitize_text_field( $_POST['legisearch_searchedbill_description'] );

	$affected_count = $wpdb->replace(
		LEGISEARCH__BILLSTRACKED_TABLENAME,
		array(
			'id'  => $id,
			'state' => $state,
			'session' => $session,
			'bill_id' => $bill_id,
			'description' => $description
		),
		array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s'
		)
	);

	if( $affected_count == 1 ) {
		$legisearch_update_msg = "Bill added";
	}
	else if( $affected_count > 1 ) {
		$legisearch_update_msg = "Bill updated";
	}
	else {
		$legisearch_error_msg = "There was an error and the bill was not added.";
	}
}
