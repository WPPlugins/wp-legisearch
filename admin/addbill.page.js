jQuery(document).ready(function() {
	jQuery("#legisearch_newbill_session").prop('disabled',true);
	jQuery("#legisearch_newbill_billinfotable").hide();
	
});

function legisearch_state_selected() {
	var state = jQuery("#legisearch_newbill_state option:selected").val();
	if(state == 0) {
		// If no state selected, clear options and disable session dropdown
		jQuery('#legisearch_newbill_session').find('option').remove();
		jQuery('#legisearch_newbill_session').prop('disabled',true);
		jQuery('#legisearch_newbill_bill_id').val('');
		jQuery('#legisearch_newbill_bill_id').prop('disabled',true);
	}
	else {
		// Otherwise, populate options and enable session dropdown
		var data = {
			'action': 'legisearch_get_sessions',
			'state': state,
			'security': nonce
		};
		jQuery.post(ajaxurl, data, function(response) {
			var sessionData = jQuery.parseJSON(response);
			jQuery('#legisearch_newbill_session').find('option').remove();
			for (var k in sessionData) {
				jQuery('#legisearch_newbill_session').append(jQuery('<option/>', {
					value: k,
					text: sessionData[k]['display_name']
				}));
		}});
		jQuery("#legisearch_newbill_session").prop('disabled',false);
		jQuery("#legisearch_newbill_bill_id").prop('disabled',false);
	}
}

function legisearch_bill_number_keyup() {
	var txt = jQuery('#legisearch_newbill_bill_id');
	var btn = jQuery('#legisearch_newbill_searchbutton');
	if(!txt.val()) {
		btn.prop('disabled',true);	
	}
	else {
		btn.prop('disabled',false);
	}
}

function legisearch_search_button_press() {
	jQuery('#legisearch_newbill_searchingimg').addClass('is-active'); // Show spinner image while we work
	var state = jQuery("#legisearch_newbill_state option:selected").val();
	var session = jQuery("#legisearch_newbill_session option:selected").val();
	var bill_id = jQuery("#legisearch_newbill_bill_id").val();
	// Get Bill information
	var data = {
		'action': 'legisearch_get_bill',
		'state': state,
		'session': session,
		'bill_id': bill_id,
		'security': nonce
	};
	jQuery.post(ajaxurl, data, function(response) {
		try {
			var billData = jQuery.parseJSON(response);
			var bill_id = billData['bill_id'];
			var bill_title = billData['title'];
			var bill_sponsors = billData['sponsors'];
			var bill_primary_sponsor = '';
			var bill_co_sponsors = Array();
			var bill_subjects = billData['subjects'];
			var bill_session_id = billData['session'];
			var bill_state = billData['state'];
			var os_id = billData['id'];
			var bill_description = billData['description'];
			for(i=0;i<bill_sponsors.length; ++i) {
				if(bill_sponsors[i]['type'] == 'primary') {
					bill_primary_sponsor = bill_sponsors[i]['name'];
				}
				else {
					bill_co_sponsors.push(bill_sponsors[i]['name']);
				}
			}
			jQuery('#legisearch_searchedbill_id').text(bill_id);
			jQuery('#legisearch_searchedbill_title').text(bill_title);
			jQuery('#legisearch_searchedbill_sponsors').text(bill_primary_sponsor);
			if(bill_co_sponsors.length > 0) {
				jQuery('#legisearch_searchedbill_sponsors').append(', ' + bill_co_sponsors.join(', '));
			}
			jQuery('#legisearch_searchedbill_subjects').text(bill_subjects.join('; '));
			jQuery('#legisearch_searchedbill_os_id').val(os_id);
			jQuery('#legisearch_searchedbill_state').val(bill_state);
			jQuery('#legisearch_searchedbill_session').val(bill_session_id);
			jQuery('#legisearch_searchedbill_bill_id').val(bill_id);
			jQuery('#legisearch_searchedbill_description').val(bill_description);
			jQuery('#legisearch_newbill_searchingimg').removeClass('is-active'); // Remove spinner image
			jQuery('#legisearch_newbill_searchingmsg').text('');
			jQuery("#legisearch_newbill_billinfotable").show();
		}
		catch(err) {// Nothing found or there was some sort of problem
			jQuery('#legisearch_newbill_searchingimg').removeClass('is-active'); // Remove spinner image
			jQuery('#legisearch_newbill_searchingmsg').text('No result.');
			jQuery("#legisearch_newbill_billinfotable").hide();
		}
	});
}
