<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

function write_the_georesult( $legislators, $state_meta ) {
	$content = "<h3 class=\"legisearch-title legisearch-georesult-title\">Your {$state_meta->name} Legislators</h3>";
	$content .= "<div class=\"legisearch-georesult\">";
	foreach( $legislators as $legislator ) {
		$content .= "<div class=\"legisearch-geo-legislator legisearch-geo-{$legislator->chamber}-legislator\" id=\"legisearch-geo-{legislator-id}\">";
		$content .= "	<a href=\"{$legislator->permalink}\">{$legislator->full_name}</a>, the {$legislator->party} {$legislator->title} for District {$legislator->district}";
		$content .= "</div>";
	}
	$content .= '</div>';
	$content .= '<div class="legisearch-googlemap" id="legisearch-googlemap"></div>';
	return $content;
}
