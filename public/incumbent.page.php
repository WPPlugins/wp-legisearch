<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

function ListPhones( $legislator ) {
	$content = '';
	foreach ($legislator->offices as $office) {
		if( isset( $office->phone ) ) {
			$content .= "<strong>{$office->name}:</strong> {$office->phone}<br />";
		}
	}
	return $content;
}

function ListEmails($legislator) {
	$content = '';
	$emails = array();
	foreach ($legislator->offices as $office) {
		if( isset( $office->email ) ) {
			$emails[] = $office;
		}
	}
	if( count( $emails ) == 0 ) {
		return '';
	}
	elseif( count( $emails ) == 1 ) {
		$content .= "<a href=\"mailto:{$emails[0]->email}\">{$emails[0]->email}</a>";
	}
	else {
		foreach( $emails as $contact ) {
			$content .= "<strong>{$contact->name} Email: </strong><a href=\"mailto:{$contact->email}\">{$contact->email}</a>";
		}
	}
	return $content;
}

function ListVotes( $legislator, $votes ) {
	$content = '<table class="legisearch-vote-table">';
	$content .= '<tr>';
	$content .= "<th >Date</th><th>Action</th><th>Description</th><th>Voted</th>";
	$content .=  "</tr>";
	foreach( $votes as $vote ) {
		$vote_cast = $vote->get_vote( $legislator );

		$class_string = 'legisearch_vote_row';
		if( $vote->bill->is_primary_sponsor( $legislator ) ) {
			$class_string .= ' sponsored-bill-vote primary-sponsored-bill-vote';
		}
		else if( $vote->bill->is_cosponsor( $legislator ) ) {
			$class_string .= ' sponsored-bill-vote cosponsored-bill-vote';
		}
		else if( $vote->bill->is_sponsor( $legislator ) ) {
			$class_string .= ' sponsored-bill-vote';
		}
		if( $vote->vote_pref_type == 'prefer' ) {
			if( $vote->vote_pref == $vote_cast ) {
				$class_string .= ' voted-with';
			}
			else if( $vote_cast == 'Y' || $vote_cast == 'N' ) {
				// Do not hold 'Other' votes against them
				$class_string .= ' voted-against';
			}
		}
		else {
			$class_string .= ' vote-neutral';
		}

		$content .= "<tr class=\"{$class_string}\">";
		$content .= '<td><center>' . date('j M <br/>Y',strtotime($vote->date)) . '</center></td>';
		$content .= "<td class=\"legisearch-vote-date\">{$vote->description} for <nobr>{$vote->bill->bill_id}</nobr></td>";
		$content .= "<td>{$vote->bill->description}</td>";
		$content .= "<td>" . $vote_cast . "</td>";
		$content .= "</tr>";
	}
	$content .= "</table>";
	return $content;
}

function write_incumbent_part( $legislator, $votes ) {
	$content = "<h3>{$legislator->full_name} </h3>";
	$content .= "<div class=\"legisearch-incumbent\">";
	$content .= "	<div class=\"legisearch-incumbent-basics\">";
	$content .= "		<div class=\"legisearch-incumbent-photo\">";
	$content .= "			<img class=\"legisearch-incumbent-photo\" src=\"{$legislator->photo_url}\" />";
	$content .= "		</div>";
	$content .= "		<div class=\"legisearch-incumbent-partydistrict\">";
	$content .= "			<ul>";
	$content .= "				<li class=\"legisearch-incumbent-title\">{$legislator->title}</li>";
	$content .= "				<li class=\"legisearch-incumbent-party\">{$legislator->party}</li>";
	$content .= "				<li class=\"legisearch-incumbent-district\">District {$legislator->district}</li>";
	$content .= "			</ul>";
	$content .= "		</div>";
	$content .= "		<div class=\"legisearch-contact-info\">";
	$content .= ListPhones( $legislator );
	$content .= ListEmails( $legislator );
	$content .= "		</div>";
	$content .= "	</div>";
	$content .= "	<div style=\"clear:both;\"></div>";
	$content .= "	<hr class=\"legisearch-after-basics\" />";
	$content .= ListVotes( $legislator, $votes );
	$content .= "</div>";
	return $content;
}

