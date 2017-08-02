<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

function write_chamber_part( $chamber_members, $state_meta ) {
	$chamber = $chamber_members->members[0]->chamber;
	$content = "<h3>{$state_meta->name} {$state_meta->chambers->$chamber->name}</h3>";
	$content .= "<ul class=\"legisearch-chamber-listing legisearch-chamber-listing-{$state_meta->abbreviation}\" >";
	foreach( $chamber_members->members as $incumbent ) {
		$content .= "	<li class=\"legisearch-chamber-item\">";
		$content .= "		<span class=\"legisearch-chamber-item-name\"><a class=\"legisearch-incumbent-link\" href=\"" . get_permalink() . "/{$state_meta->abbreviation}/legislators/{$incumbent->id}/\">{$incumbent->full_name}</a></span>";
		$content .= "<span class=\"legisearch-chamber-item-district\">{$state_meta->chambers->$chamber->name} District {$incumbent->district}</span>";
		$content .= "	</li>";
	}
	$content .= "</ul>";
	return $content;
}
