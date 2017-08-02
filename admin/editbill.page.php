<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Bill.class.php' );
	require_once( LEGISEARCH__PLUGIN_DIR . '/admin/editbill.action.php' );

	$id = $_GET['bill'];
	$bill = legisearch_get_trackedbill_by_id( $id );

	if( empty( $bill ) ) {
		wp_die( "That bill could not be found" );
	}
?>
<div class="wrap">
	<h1>Edit <?php echo $bill->bill_id; ?> </h1>
	<h3><?php echo $bill->state_name; ?>: <?php echo $bill->session_display_name; ?></h3>
	<?php legisearch_show_messages(); ?>
	<form method="POST" action="">
	<input type="hidden" name="legisearch_bill_id" value="<?php echo $bill->bill_id; ?>" />
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="legisearch_bill_description">Your Bill Description</th>
			<td>
				<input style="width:250px" name="legisearch_bill_description" id="legisearch_bill_description" value="<?php echo stripslashes( $bill->description ); ?>" /><br />
				<small><em>This is the name that will be pubicly shown</em></small>
			</td>
		</tr>
	</table>
	<table id="legisearch_editbill_billinfotable" class="widefat">
	<thead>
		<tr>
			<th>Show</th>
			<th>Date/Time</th>
			<th>Chamber</th>
			<th>Official Description</th>	
			<th>Result</th>
			<th>Your Vote Description</th>
			<th>Your Position</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th>Show</th>
			<th>Date/Time</th>
			<th>Chamber</th>
			<th>Official Description</th>
			<th>Result</th>
			<th>Your Vote Description</th>
			<th>Your Position</th>
		</tr>
	</tfoot>
	<tbody>
		<?php foreach( $bill->votes as $vote ): ?>
		<?php $vote_datetime = date( 'j M Y h:i a', strtotime( $vote->date ) );	?>
		<tr valign="top">
			<td>
				<input type="hidden" name="legisearch_vote_ids[]" value="<?php echo $vote->id; ?>" />
				<input type="checkbox" name="legisearch_track_votes[]" value="<?php echo $vote->id; ?>" <?php echo ($vote->tracked) ? "checked" : "" ?> />
			</td>
			<td><?php echo $vote_datetime; ?></td>
			<td><?php echo $vote->chamber; ?></td>
			<td><?php echo $vote->motion; ?></td>
			<td><?php echo $vote->yes_count; ?>-<?php echo $vote->no_count; ?> <?php echo ($vote->passed) ? "Passed" : "Failed" ?></td>
			<td><input style="width:250px" name="legisearch_vote_descriptions[]" id="legisearch_vote_description" value="<?php echo stripslashes( $vote->description ); ?>" /></td>
			<td>
				<select class="legisearch_vote_position_type" name="legisearch_vote_position_types[]">
					<option value="neutral">Neutral</option>
					<option value="prefer"<?php if( $vote->vote_pref_type == 'prefer' ) echo ' selected'; ?>>Prefer Vote Of</option>
				</select>
				<select class="legisearch_vote_pref" name="legisearch_vote_prefs[]">
					<option value="Y"<?php if( $vote->vote_pref == 'Y' ) echo ' selected'; ?>>Y</option>
					<option value="N"<?php if( $vote->vote_pref == 'N' ) echo ' selected'; ?>>N</option>
				</select>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	</table>
<?php wp_nonce_field( 'legisearch_edit_tracked_bill','legisearch_action' ); ?>
<input type="submit" value="Update All" name="legisearch_bill_editbutton" class="button-primary" onclick="editbill(); return false;" />
	</form>
