<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Bill.class.php' );

if( isset( $_POST['legisearch_bill_editbutton'] ) ) {
	// Security. This method has the ability to alter the database.
	if ( !check_admin_referer( 'legisearch_edit_tracked_bill','legisearch_action' ) ) {
		wp_die( 'Security issue detected' );	
	}
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions' );	
	}

	global $legisearch_update_msg, $legisearch_error_msg;
	
	$id = $_GET['bill'];
	$bill = legisearch_get_trackedbill_by_id( $id );

	$description = $_POST['legisearch_bill_description'];
	$bill->description = $description;

	$vote_ids = $_POST['legisearch_vote_ids'];
	$vote_descriptions = $_POST['legisearch_vote_descriptions'];
	$vote_positiontypes = $_POST['legisearch_vote_position_types'];
	$vote_prefs = $_POST['legisearch_vote_prefs'];
	$votes = $bill->votes;

	foreach( $votes as $vote ) {
		// We're going through the Openstates records and matching to the new data
		$key = array_search( $vote->id, $vote_ids );
		if( $key !== false) {
			$vote->description = $vote_descriptions[$key];
			$vote->track = legisearch_isChecked( 'legisearch_track_votes', $vote_ids[$key] );
			$vote->vote_pref_type = $vote_positiontypes[$key];
			$vote->vote_pref = $vote_prefs[$key];
		}
	}
	$legisearch_update_msg = "Bill Data Saved";
}

function legisearch_isChecked( $chkname, $value ) {
	if(!empty($_POST[$chkname])) {
		foreach($_POST[$chkname] as $chkval) {
			if($chkval == $value) {
				return true; 
			}
		}
	}
	return false;
}
