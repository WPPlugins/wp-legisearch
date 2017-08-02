<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/StateMetaData.class.php' );
require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/iOsData.interface.php' );

class Legislator implements iOsData {

	private $_os_data = null;
	private $_state_os_data = null;

	public static function get_disticts_by_geo( $lat, $lon ) {
		// Get plugin settings
		$legisearch_settings = LegisearchSettings::getInstance();
		$apikey = $legisearch_settings->os_api_key;
		$cache_timeout = $legisearch_settings->os_cache_timeout;
		$cache_tablename = LEGISEARCH__OSCACHE_TABLENAME;

		// Dependency Injection
		$json_cacheable = new WpJsonCacheable( $cache_timeout, $cache_tablename );
		$os_client = new OsClient( $legisearch_settings->os_apikey, $json_cacheable );

		$fields = array( 'state', 'chamber', 'district' );
		$json_return = json_decode( $os_client->get_legislator_geo( $lat, $lon ) );
		if( $json_return ) {
			return $json_return;
		}
		else {
			return false;
		}
	}

	public static function get_legislators_by_district( $state, $chamber, $district ) {
		$legislators = array();

		// Get plugin settings
		$legisearch_settings = LegisearchSettings::getInstance();
		$apikey = $legisearch_settings->os_api_key;
		$cache_timeout = $legisearch_settings->os_cache_timeout;
		$cache_tablename = LEGISEARCH__OSCACHE_TABLENAME;

		// Dependency Injection
		$json_cacheable = new WpJsonCacheable( $cache_timeout, $cache_tablename );
		$os_client = new OsClient( $legisearch_settings->os_apikey, $json_cacheable );
		$search_parameters = array(
			'state' => $state,
			'active' => 'true',
			'chamber' => $chamber,
			'district' => $district);
		$json_return = json_decode( $os_client->get_legislator_search( $search_parameters ) );
		if( !empty( $json_return ) ) {
			foreach( $json_return as $obj ) {
				$legislators[] = new Legislator( $obj->id );
			}
		}
		return $legislators;
	}

	public function __construct( $id ) {
		// Get plugin settings
		$legisearch_settings = LegisearchSettings::getInstance();
		$apikey = $legisearch_settings->os_api_key;
		$cache_timeout = $legisearch_settings->os_cache_timeout;
		$cache_tablename = LEGISEARCH__OSCACHE_TABLENAME;

		// Dependency Injection
		$json_cacheable = new WpJsonCacheable( $cache_timeout, $cache_tablename );
		$os_client = new OsClient( $legisearch_settings->os_apikey, $json_cacheable );
		$json_return = json_decode( $os_client->get_legislator_lookup( $id ) );
		if( !empty( $json_return ) ) {
			$this->_os_data = $json_return;
			$json_return = json_decode( $os_client->get_state_metadata( $this->_os_data->state ) );
			if( !empty( $json_return ) ) {
				$this->_state_os_data = $json_return;
			}
		}
	}

	public function __get( $name ) {
		try {
			switch( $name ) {
				case 'permalink':
					$state = $this->state;
					return get_permalink( ) . "/{$state}/legislators/{$this->id}";
					break;
				case 'title':
					$chamber = $this->chamber;
					return $this->_state_os_data->chambers->$chamber->title;
					break;
				case 'photo_url':
					return $this->get_photo_url();
				default:
					return $this->_os_data->$name;
					break;
			}
		}
		catch( Exception $e ) {
			return false;
		}
	}

	public function __set( $name, $value ) {
		// Read only Data
	}

	public function is_osvalid() {
		return !empty( $this->_os_data );
	}

	public function get_primary_sponsored_tracked_bills() {
		$tracked_bills = legisearch_get_all_tracked_bills();
		$primary_sponsored_bills = array();
		foreach( $tracked_bills as $bill ) {
			if( $bill->is_primary_sponsor( $this ) ) {
				$primary_sponsored_bills[] = new Bill( $bill->state, $bill->session, $bill->bill_id );
			}
		}
		return $primary_sponsored_bills;
	}

	public function get_cosponsored_tracked_bills() {
		$tracked_bills = legisearch_get_all_tracked_bills();
		$cosponsored_bills = array();
		foreach( $tracked_bills as $bill ) {
			if( $bill->is_cosponsor( $this ) ) {
				$cosponsored_bills[] = new Bill( $bill->state, $bill->session, $bill->bill_id );
			}
		}
		return $cosponsored_bills;
	}

	public function get_photo_url() {
		$photo_url = $this->_os_data->photo_url;
		// Determine if file exists in cache
		$photo_filename_info = pathinfo( $photo_url );
		$photo_filename = $photo_filename_info['filename'] . '.' . $photo_filename_info['extension'];
		$photo_cache_dir = LEGISEARCH__PLUGIN_DIR . '/imagecache/' . $this->state . '.';
		$photo_cache_filepath = $photo_cache_dir . $photo_filename;
		$photo_cache_url = LEGISEARCH__PLUGIN_URL . '/imagecache/' . $this->state . ".{$photo_filename}";
		if( !file_exists( $photo_cache_filepath ) ) {
			// If it doesn't, download it
			$this->download_file( $photo_url, $photo_cache_filepath );
		}
		// Return the cached file if it worked:
		if( file_exists( $photo_cache_filepath ) ) {
			return $photo_cache_url;
		}
		else {
			return $this->_os_data->photo_url;
		}
	}

	private function download_file ($url, $path) {
		$newfname = $path;
		$file = @fopen ($url, "rb");
		if ( !empty( $file ) ) {
			$newf = @fopen ($newfname, "wb"); // Suppress warnings or it will become part of the filepath
			if ($newf) {
				while(!feof($file)) {
					fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
				}
			}
		}
		if ($file) {
			fclose($file);
		}

		if ( isset( $newf ) && $newf ) {
			fclose($newf);
		}
	}

	public function get_all_tracked_votes() {
		require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Bill.class.php' );
		global $wpdb;
		$sql = "SELECT * FROM " . LEGISEARCH__BILLSTRACKED_TABLENAME . " WHERE state='{$this->state}'";
		$rows = $wpdb->get_results( $sql );
		$tracked_votes = array();
		foreach( $rows as $row ) {
			$bill = new Bill( $this->state, $row->session, $row->bill_id );
			$votes = $bill->get_tracked_votes();
			foreach( $votes as $vote ) {
				if( $vote->get_vote( $this ) ) {
					$tracked_votes[] = $vote;
				}
			}
		}
		usort( $tracked_votes, array( $this, 'sort_votes_rchron' ) );
		return $tracked_votes;
	}

	private function sort_votes_rchron( $a, $b ) {
		return strtotime( $b->date ) - strtotime( $a->date ) ;
	}

	private function legisearch_get_all_tracked_bills( ) {
		require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Bill.class.php' );
		global $wpdb;

		$tracked_bills = array();
		$sql = "SELECT * FROM " . LEGISEARCH__BILLSTRACKED_TABLENAME . " WHERE 1;";
		$result = $wpdb->get_results( $sql, OBJECT_K );
		krsort( $result ); // Reverse sort by keys (should be newest first)
		foreach( $result as $row ) {
			$tracked_bills[] = new Bill( $row->state, $row->session, $row->bill_id );
		}
		return $tracked_bills;
	}
}
