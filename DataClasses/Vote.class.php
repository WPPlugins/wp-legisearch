<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/iOsData.interface.php' );

class Vote implements iOsData {
	private $_os_data = null;
	private $_ls_data = null;

	public function __construct( $bill_os_id, $vote_data ) {
		// Use $vote_data as _os_data
		$this->_os_data = $vote_data;

		// Get Bill Data from Database
		global $wpdb;
		$sql = "SELECT * FROM " . LEGISEARCH__VOTESTRACKED_TABLENAME . " WHERE id = '{$this->_os_data->id}';";
		$row = $wpdb->get_row( $sql );
		if( $wpdb->num_rows == 1 ) {
			$this->_ls_data = $row;
		}
		else {
			// If the record doesn't exist, create it.
			$wpdb->insert(LEGISEARCH__VOTESTRACKED_TABLENAME,
				array( 'id' => $this->_os_data->id, 'bill_os_id' => $bill_os_id, 'description' => '', 'track' => 0 ),
				array( '%s', '%s', '%s', '%d' )
			);
			$this->_ls_data = $wpdb->get_row( $sql );
		}
	}

	public function __get( $name ) {
		switch( $name ) {
			case 'description':
			case 'track':
			case 'vote_pref_type':
			case 'vote_pref':
			// bill_id is overriding Openstates, which gives their internal ID. This isn't great but it ambiguous since they have the same 				// field name.
			case 'bill_os_id': 
				if ( $this->_ls_data ) {
					return stripslashes( $this->_ls_data->$name );
				}
				else {
					// If it's not in the database, it's as if it is not tracked with no description
					return '';					
				}
				break;
			// The database field name is "track," but "tracked" makes more sense. Maybe database should be altered.
			case 'tracked': 
				return $this->track;
				break;
			case 'bill':
				require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
				return Bill::get_by_bill_id( $this->bill_id );
			default:
				return $this->_os_data->$name;
				break;
		}
	}

	public function __set( $name, $value ) {
		global $wpdb;
		if( $name == 'description' ) {
			$description = sanitize_text_field( $value );
			$affected_count = $wpdb->update(
				LEGISEARCH__VOTESTRACKED_TABLENAME,
				array( 'description' => $description ),
				array( 'id' => $this->id ),
				array( '%s' ),
				array( '%s' )
			);
		}
		if( $name == 'track' ) {
			$track = intval( $value );
			$affected_count = $wpdb->update(
				LEGISEARCH__VOTESTRACKED_TABLENAME,
				array( 'track' => $track ),
				array( 'id' => $this->id ),
				array( '%d' ),
				array( '%s' )
			);
		}
		if( $name == 'vote_pref_type' ) {
			$preference_enum_val = 'neutral';
			if( $value == 'prefer' ) {
				$preference_enum_val = 'prefer';
			}
			$affected_count = $wpdb->update(
				LEGISEARCH__VOTESTRACKED_TABLENAME,
				array( 'vote_pref_type' => $preference_enum_val ),
				array( 'id' => $this->id ),
				array( '%s' ),
				array( '%s' )
			);
		}
		if( $name == 'vote_pref' ) {
			$preference_val = substr( $value, 0, 1 );
			$affected_count = $wpdb->update(
				LEGISEARCH__VOTESTRACKED_TABLENAME,
				array( 'vote_pref' => $preference_val ),
				array( 'id' => $this->id ),
				array( '%s' ),
				array( '%s' )
			);
		}
	}

	public function get_vote( $legislator) {
		foreach( $this->_os_data->other_votes as $leg ) {
			if( in_array( $leg->leg_id, $legislator->all_ids ) ) {
				return 'Other';
			}
		}
		foreach( $this->_os_data->yes_votes as $leg ) {
			if( in_array( $leg->leg_id, $legislator->all_ids ) ) {
				return 'Y';
			}
		}
		foreach( $this->_os_data->no_votes as $leg ) {
			if( in_array( $leg->leg_id, $legislator->all_ids ) ) {
				return 'N';
			}
		}
		return false;
	}

	public function is_osvalid() {
		return !empty( $this->_os_data );
	}
}
