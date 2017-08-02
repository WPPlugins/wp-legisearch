<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/StateMetaData.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Vote.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . "/os-client/OsClient.class.php" );
require_once( LEGISEARCH__PLUGIN_DIR . "/os-client/WpJsonCacheable.class.php" );
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/iOsData.interface.php' );

class Bill implements iOsData {

	private $_os_data = null;
	private $_os_statemeta_data = null;
	private $_ls_data = null;

	public static function get_by_bill_id( $id ) {
		global $wpdb;
		$sql = "SELECT * FROM " . LEGISEARCH__BILLSTRACKED_TABLENAME . " WHERE id = '{$id}';";
		$row = $wpdb->get_row( $sql );
		return new Bill( $row->state, $row->session, $row->bill_id );
	}

	public function __construct( $state, $session, $bill_id ) {
		// Get plugin settings
		$legisearch_settings = LegisearchSettings::getInstance();
		$apikey = $legisearch_settings->os_api_key;
		$cache_timeout = $legisearch_settings->os_cache_timeout;
		$cache_tablename = LEGISEARCH__OSCACHE_TABLENAME;
		
		// DI object
		$json_cacheable = new WpJsonCacheable( $cache_timeout, $cache_tablename );
		$os_client = new OsClient( $legisearch_settings->os_apikey, $json_cacheable );

		// Get Bill Data from Openstates
		$state = trim( strtolower( $state ) );
		$json_return = json_decode( $os_client->get_bill_lookup( $state, $session, $bill_id ) );
		if( $json_return ) {
			$this->_os_data = $json_return;

			// Get Bill Data from Database
			$os_id = $this->_os_data->id;
			global $wpdb;
			$sql = "SELECT * FROM " . LEGISEARCH__BILLSTRACKED_TABLENAME . " WHERE id = '{$this->_os_data->id}';";
			$row = $wpdb->get_row( $sql );
			if( $wpdb->num_rows == 1 ) {
				$this->_ls_data = $row;
			}
		}

		// Get State Meta Data from Openstates
		$json_return = json_decode( $os_client->get_state_metadata( $state ) );
		if( $json_return ) {
			$this->_os_statemeta_data = $json_return;
		}
	}

	public function __get( $name ) {
		switch( $name ) {
			case 'description':
				return stripslashes( $this->_ls_data->$name );
				break;
			case 'state_name':	// Special case - we like user-friendly state names sometimes
				return $this->_os_statemeta_data->name;
				break;
			case 'session_display_name':	// Special case - 2015rs is not very helpful
				$session = $this->session;
				return $this->_os_statemeta_data->session_details->$session->display_name;
				break;
			case 'votes':
				$votes = array();
				foreach( $this->_os_data->votes as $vote_data ) {
					$votes[] = new Vote( $this->id, $vote_data );
				}
				return $votes;
				break;
			default:
				return $this->_os_data->$name;
				break;
		}
	}

	public function is_primary_sponsor( $legislator ) {
		$os_sponsors = $this->_os_data->sponsors;
		foreach( $os_sponsors as $os_sponsor ) {
			if( $os_sponsor->type == 'primary' && $os_sponsor->leg_id == $legislator->leg_id ) {
				return true;
			}
		}
		return false;
	}

	public function is_cosponsor( $legislator ) {
		$os_sponsors = $this->_os_data->sponsors;
		foreach( $os_sponsors as $os_sponsor ) {
			if( $os_sponsor->type == 'cosponsor' && $os_sponsor->leg_id == $legislator->leg_id ) {
				return true;
			}
		}
		return false;
	}

	public function is_sponsor( $legislator ) {
		$os_sponsors = $this->_os_data->sponsors;
		foreach( $os_sponsors as $os_sponsor ) {
			if( $os_sponsor->leg_id == $legislator->leg_id ) {
				return true;
			}
		}
		return false;
	}

	public function __set( $name, $value ) {
		global $wpdb;
		if( $name == 'description' ) {
			$description = sanitize_text_field( $value );
			$affected_count = $wpdb->replace(
				LEGISEARCH__BILLSTRACKED_TABLENAME,
				array(
					'id'  => $this->id,
					'state' => $this->state,
					'session' => $this->session,
					'bill_id' => $this->bill_id,
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
		}
	}

	public function is_osvalid() {
		return !empty( $this->_os_data );
	}

	public function get_tracked_votes() {
		global $wpdb;
		$tracked_votes = array();
		foreach( $this->votes as $vote ) {
			$sql = "SELECT id FROM " . LEGISEARCH__VOTESTRACKED_TABLENAME . " WHERE track = 1 AND id = '$vote->vote_id';";
			$row = 	$wpdb->get_row( $sql );
			if( $wpdb->num_rows == 1 ) {
				$tracked_votes[] = $vote;
			}
		}
		return $tracked_votes;	
	}
}
