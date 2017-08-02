<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	require_once( 'addbill.action.php' );
	wp_enqueue_script( 
		'addbill.page.js', 
		LEGISEARCH__PLUGIN_URL . '/admin/addbill.page.js',
		'jQuery');
?>

<?php
//Set Nonce
$ajax_nonce = wp_create_nonce( "legisearch_addbill" );
echo "<script type=\"text/javascript\">var nonce='{$ajax_nonce}'</script>";
?>

<div class="wrap">
	<h1>Add New Bill</h1>
	<?php legisearch_show_messages(); ?>
	<h3>Search</h3>
	<form method="POST" action="#">
	<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="legisearch_newbill_state">State</label></th>
		<td>
			<select id="legisearch_newbill_state" name="legisearch_newbill_state" onchange="legisearch_state_selected()">
				<option value="0"></option><option value="AL">Alabama</option><option value="AK">Alaska</option><option value="AZ">Arizona</option><option value="AR">Arkansas</option><option value="CA">California</option><option value="CO">Colorado</option><option value="CT">Connecticut</option><option value="DE">Delaware</option><option value="DC">District Of Columbia</option><option value="FL">Florida</option><option value="GA">Georgia</option><option value="HI">Hawaii</option><option value="ID">Idaho</option><option value="IL">Illinois</option><option value="IN">Indiana</option><option value="IA">Iowa</option><option value="KS">Kansas</option><option value="KY">Kentucky</option><option value="LA">Louisiana</option><option value="ME">Maine</option><option value="MD">Maryland</option><option value="MA">Massachusetts</option><option value="MI">Michigan</option><option value="MN">Minnesota</option><option value="MS">Mississippi</option><option value="MO">Missouri</option><option value="MT">Montana</option><option value="NE">Nebraska</option><option value="NV">Nevada</option><option value="NH">New Hampshire</option><option value="NJ">New Jersey</option><option value="NM">New Mexico</option><option value="NY">New York</option><option value="NC">North Carolina</option><option value="ND">North Dakota</option><option value="OH">Ohio</option><option value="OK">Oklahoma</option><option value="OR">Oregon</option><option value="PA">Pennsylvania</option><option value="RI">Rhode Island</option><option value="SC">South Carolina</option><option value="SD">South Dakota</option><option value="TN">Tennessee</option><option value="TX">Texas</option><option value="UT">Utah</option><option value="VT">Vermont</option><option value="VA">Virginia</option><option value="WA">Washington</option><option value="WV">West Virginia</option><option value="WI">Wisconsin</option><option value="WY">Wyoming</option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="legisearch_newbill_session">Session</label></th>
		<td>
			<select name="legisearch_newbill_session" id="legisearch_newbill_session" style="width:250px">
				<option value="0"></option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="legisearch_newbill_bill_id">Bill Number</label></th>
		<td>
			<input name="legisearch_newbill_bill_id" id="legisearch_newbill_bill_id" onkeyup="legisearch_bill_number_keyup()" disabled /><br />
			<small><em>i.e. HB 3, SB 214, etc.</em></small>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<input type="submit" name="legisearch_newbill_searchbutton" value="Search" id="legisearch_newbill_searchbutton" class="button-primary" onclick="legisearch_search_button_press(); return false;" disabled />
		</th>
		<td>
			<div id="legisearch_newbill_searchingimg" class="spinner" style="float:left;"></div>
			<div id="legisearch_newbill_searchingmsg" style="float:left;"></div>
		</td>
	</tr>
	</table>
	</form>
	
	<form method="POST" action="#">
	<input type="hidden" id="legisearch_searchedbill_os_id" name="legisearch_searchedbill_os_id" value="" />
	<input type="hidden" id="legisearch_searchedbill_state" name="legisearch_searchedbill_state" value="" />
	<input type="hidden" id="legisearch_searchedbill_session" name="legisearch_searchedbill_session" value="" />
	<input type="hidden" id="legisearch_searchedbill_bill_id" name="legisearch_searchedbill_bill_id" value="" />
	<?php wp_nonce_field( 'legisearch_add_tracked_bill','legisearch_action' ); ?>

	<table id="legisearch_newbill_billinfotable" style="min-height:160px; max-width: 600px;" class="widefat">
	<thead>
		<tr>
			<th><h2><span id="legisearch_searchedbill_id"></h2></th>
			<th id="legisearch_searchedbill_addbutton"><input type="submit" value="Add/Update" name="legisearch_searchedbill_addbutton" class="button-primary" onclick="addbill(); return false;" /></th>
		</tr>
	</thead>
	<tbody>
		<td colspan="2">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="legisearch_searchedbill_description">Your Description</th>
				<td>
					<input style="width:250px" name="legisearch_searchedbill_description" id="legisearch_searchedbill_description" /><br />
					<small><em>This is the name that will be pubicly shown</em></small>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="legisearch_searchedbill_sponsors">Sponsors</th>
				<td id="legisearch_searchedbill_sponsors"></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="legisearch_searchedbill_subjects">Subjects</th>
				<td id="legisearch_searchedbill_subjects"></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="legisearch_searchedbill_title">Official Title</th>
				<td id="legisearch_searchedbill_title"></td>
			</tr>
		</table>
		</td>
	</tbody>
	</table>
	</form>
</div>
