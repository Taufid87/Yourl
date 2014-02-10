<?php
// This file initialize everything needed for YOURLS

// Include settings
if( file_exists( dirname( dirname( __FILE__ ) ) . '/user/config.php' ) ) {
	// config.php in /user/
	define( 'YOURLS_CONFIGFILE', str_replace( '\\', '/', dirname( dirname( __FILE__ ) ) ) . '/user/config.php' );
} elseif ( file_exists( dirname( __FILE__ ) . '/config.php' ) ) {
	// config.php in /includes/
	define( 'YOURLS_CONFIGFILE', str_replace( '\\', '/', dirname( __FILE__ ) ) . '/config.php' );
} else {
	// config.php not found :(
	die( '<p class="error">Cannot find <code>config.php</code>.</p><p>Please read the <a href="../docs/#install">documentation</a> to learn how to install YOURLS</p>' );
}
require_once YOURLS_CONFIGFILE;

// Check if config.php was properly updated for 1.4
if( !defined( 'YOURLS_DB_PREFIX' ) )
	die( '<p class="error">Your <code>config.php</code> does not contain all the required constant definitions.</p><p>Please check <code>config-sample.php</code> and update your config accordingly, there are new stuffs!</p>' );

	
// Define core constants that have not been user defined in config.php
$yourls_definitions = array(
    // physical path of YOURLS root
    'YOURLS_ABSPATH'             => str_replace( '\\', '/', dirname( dirname( __FILE__ ) ) ),
    // physical path of includes directory
    'YOURLS_INC'                 => array( 'YOURLS_ABSPATH', '/includes' ),

    // physical path and url of asset directory
    'YOURLS_ASSETDIR'            => array( 'YOURLS_ABSPATH', '/assets' ),
    'YOURLS_ASSETURL'            => array( 'YOURLS_SITE', '/assets' ),

    // physical path and url of user directory
    'YOURLS_USERDIR'             => array( 'YOURLS_ABSPATH', '/user' ),
    'YOURLS_USERURL'             => array( 'YOURLS_SITE', '/user' ),
    // physical path of translations directory
    'YOURLS_LANG_DIR'            => array( 'YOURLS_USERDIR', '/languages' ),
    // physical path and url of plugins directory
    'YOURLS_PLUGINDIR'           => array( 'YOURLS_USERDIR', '/plugins' ),
    'YOURLS_PLUGINURL'           => array( 'YOURLS_USERURL', '/plugins' ),
    // physical path and url of themes directory
    'YOURLS_THEMEDIR'            => array( 'YOURLS_USERDIR', '/themes' ),
    'YOURLS_THEMEURL'            => array( 'YOURLS_USERURL', '/themes' ),
    // physical path of pages directory
    'YOURLS_PAGEDIR'             => array( 'YOURLS_USERDIR', '/pages' ),

    // admin pages location
    'YOURLS_ADMIN_LOCATION'           => 'admin',

    // table to store URLs
    'YOURLS_DB_TABLE_URL'        => array( 'YOURLS_DB_PREFIX', 'url' ),
    // table to store options
    'YOURLS_DB_TABLE_OPTIONS'    => array( 'YOURLS_DB_PREFIX', 'options' ),
    // table to store hits, for stats
    'YOURLS_DB_TABLE_LOG'        => array( 'YOURLS_DB_PREFIX', 'log' ),

    // minimum delay in sec before a same IP can add another URL. Note: logged in users are not throttled down.
    'YOURLS_FLOOD_DELAY_SECONDS' => 15,
    // comma separated list of IPs that can bypass flood check.
    'YOURLS_FLOOD_IP_WHITELIST'  => '',
    'YOURLS_COOKIE_LIFE'         => 60*60*24*7,
    // life span of a nonce in seconds
    'YOURLS_NONCE_LIFE'          => 43200, // 3600 *,12

    // if set to true, disable stat logging (no use for it, too busy servers, ...)
    'YOURLS_NOSTATS'             => false,
    // if set to true, force https:// in the admin area
    'YOURLS_ADMIN_SSL'           => false,
    // if set to true, verbose debug infos. Will break things. Don't enable.
    'YOURLS_DEBUG'               => false,
);

foreach ( $yourls_definitions as $const_name => $const_default_value ) {
	if( !defined( $const_name ) ) {
		if ( is_array( $const_default_value ) ) {
			define( $const_name, constant( $const_default_value[0] ) . $const_default_value[1] );
		} else {
			define( $const_name, $const_default_value );
		}
	}
}
	
// Error reporting
if( defined( 'YOURLS_DEBUG' ) && YOURLS_DEBUG == true ) {
	error_reporting( -1 );
} else {
	error_reporting( E_ERROR | E_PARSE );
}

// Include needed libraries
require YOURLS_INC . '/vendor/autoload.php';

// Include all functions
require_once YOURLS_INC . '/version.php';
require_once YOURLS_INC . '/functions.php';
require_once YOURLS_INC . '/functions-log.php';
require_once YOURLS_INC . '/functions-plugins.php';
require_once YOURLS_INC . '/functions-formatting.php';
require_once YOURLS_INC . '/functions-api.php';
require_once YOURLS_INC . '/functions-kses.php';
require_once YOURLS_INC . '/functions-l10n.php';
require_once YOURLS_INC . '/functions-compat.php';
require_once YOURLS_INC . '/functions-html.php';
require_once YOURLS_INC . '/functions-http.php';
require_once YOURLS_INC . '/functions-infos.php';
require_once YOURLS_INC . '/functions-themes.php';

// Load auth functions if needed
if( yourls_is_private() )
	require_once YOURLS_INC.'/functions-auth.php';

$logger = new Log( 'LOADER' );
$logger->addInfo( 'Welcome to YOURLS!', array( 'Version' => YOURLS_VERSION ) );

// Load locale
yourls_load_default_textdomain();

// Check if we are in maintenance mode - if yes, it will die here.
yourls_check_maintenance_mode();

// Fix REQUEST_URI for IIS
yourls_fix_request_uri();
	
// If request for an admin page is http:// and SSL is required, redirect
if( yourls_is_admin() && yourls_needs_ssl() && !yourls_is_ssl() ) {
	if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {
		yourls_redirect( preg_replace( '|^http://|', 'https://', $_SERVER['REQUEST_URI'] ) );
		exit();
	} else {
		yourls_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		exit();
	}
}

// Create the YOURLS object $ydb that will contain everything we globally need
global $ydb;

// Allow drop-in replacement for the DB engine
if( file_exists( YOURLS_USERDIR . '/db.php' ) ) {
	require_once YOURLS_USERDIR . '/db.php';
} else {
	require_once YOURLS_INC . '/class-mysql.php';
	yourls_db_connect();
}

// Allow early inclusion of a cache layer
if( file_exists( YOURLS_USERDIR . '/cache.php' ) )
	require_once YOURLS_USERDIR . '/cache.php';

// Read options right from start
yourls_get_all_options();

// Register shutdown function
register_shutdown_function( 'yourls_shutdown' );

// Core now loaded
yourls_do_action( 'init' ); // plugins can't see this, not loaded yet

// Check if need to redirect to install procedure
if( !yourls_is_installed() && !yourls_is_installing() ) {
	yourls_redirect( YOURLS_SITE .'/yourls-install.php', 302 );
}

// Check if upgrade is needed (bypassed if upgrading or installing)
if ( !yourls_is_upgrading() && !yourls_is_installing() ) {
	if ( yourls_upgrade_is_needed() ) {
		yourls_redirect( yourls_admin_url( 'upgrade' ), 302 );
	}
}

// Init all plugins
yourls_load_plugins();
yourls_do_action( 'plugins_loaded' );

// Init themes if applicable
if( yourls_has_interface() ) {
	yourls_init_theme();
	yourls_do_action( 'init_theme' );
}

// Is there a new version of YOURLS ?
yourls_new_core_version_notice();

if( yourls_is_admin() ) {
	yourls_do_action( 'admin_init' );
}
