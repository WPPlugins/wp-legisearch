<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// The base URL to the API
define( 'OSAPI_URL', 'http://openstates.org/api/v1/' );

// Specific API call URLs
define( 'OSAPI_URL_METADATAOVERVIEW', OSAPI_URL . 'metadata/' );
define( 'OSAPI_URL_STATEMETADATA', OSAPI_URL . 'metadata/' );
define( 'OSAPI_URL_BILLSEARCH', OSAPI_URL . 'bills/' );
define( 'OSAPI_URL_BILLLOOKUP', OSAPI_URL . 'bills/' );
define( 'OSAPI_URL_LEGISLATORSEARCH', OSAPI_URL . 'legislators/' );
define( 'OSAPI_URL_LEGISLATORLOOKUP', OSAPI_URL . 'legislators/' );
define( 'OSAPI_URL_LEGISLATORGEO', OSAPI_URL . 'legislators/geo' );
define( 'OSAPI_URL_DISTRICTSEARCH', OSAPI_URL . 'districts/' );
define( 'OSAPI_URL_DISTRICTBOUNDARYLOOKUP', OSAPI_URL . 'districts/boundary/' );
