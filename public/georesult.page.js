var gmData;

jQuery(document).ready( function() {
	var url_components = window.location.href.split(/\/(al|ak|az|ar|ca|co|ct|de|dc|fl|ga|hi|id|il|in|ia|ks|ky|la|me|md|ma|mi|mn|ms|mo|mt|ne|nv|nh|nj|nm|ny|nc|nd|oh|ok|or|pa|ri|sc|sd|tn|tx|ut|vt|va|wa|wv|wi|wy)\//);
	var state = url_components[1];
	var arg_components = url_components[2].replace(/\/$/,'').split("/");
	var chamber_a = arg_components[0];
	var district_a = arg_components[1];
	var chamber_b = arg_components[2];
	var district_b = arg_components[3];
	var chamber_c = arg_components[4];
	var district_c = arg_components[5];
	
	var data = {
		'action': 'get_gmap_settings',
		'state': state,
		'chamber_a': chamber_a,
		'district_a': district_a,
		'chamber_b': chamber_b,
		'district_b': district_b,
		'chamber_c': chamber_c,
		'district_c': district_c
	}
	jQuery.post(ajaxurl, data, function(response) {
		gmData = jQuery.parseJSON(response);
		var script = document.createElement("script");
		script.src = gmData['url'];
		script.type = "text/javascript";
		document.getElementsByTagName("head")[0].appendChild(script);
	});
});


function initMap() {	
	map = new google.maps.Map(document.getElementById('legisearch-googlemap'), {
    		center: {lat: gmData['chambers'][0]['centerlat'], lng: gmData['chambers'][0]['centerlon']},
    		zoom: 9
	});	


	for(var a=0; a < gmData['chambers'].length; a++ ) {
		for( var b=0; b< gmData['chambers'][a]['boundaries'].length; b++ ) {
			for( var c=0; c< gmData['chambers'][a]['boundaries'][b].length; c++ ) {
				var chamber_coords = new Array();
				for( var d=0; d< gmData['chambers'][a]['boundaries'][b][c].length; d++ ) {			
					chamber_coords.push( {lat: gmData['chambers'][a]['boundaries'][b][c][d][1], lng: gmData['chambers'][a]['boundaries'][b][c][d][0] } );
				}
				// This handles multi-polygons
				var chamber_polygon = new google.maps.Polygon({
					paths: chamber_coords,
					strokecolor: gmData['chambers'][a]['color'],
					strokeOpacity: 0.8,
					strokeWeight: 2,
					fillColor: gmData['chambers'][a]['color'],
					fillOpacity: 0.35
				});
				chamber_polygon.setMap(map);
			}
		}
	}
}
