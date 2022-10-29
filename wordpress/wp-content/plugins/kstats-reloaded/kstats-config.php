<?php
/**
 * Configuration settings used by kStats, including constant defintions
 * addition of WP actions API and any library functions necessary for
 * loading kStats.
 *
 * @package kStatsReloaded
 * @subpackage Core
 */

/**
 * kStats Constant Definitions
 */

// Our database tables
define( 'KSTATS_TABLE_RAW',       $wpdb->prefix . 'kstats_raw' ); // Raw data, like the name suggests
define( 'KSTATS_TABLE_TOTALS',    $wpdb->prefix . 'kstats_totals' ); // Aggregate data, including totals, monthly and daily
define( 'KSTATS_TABLE_CHARTS',    $wpdb->prefix . 'kstats_charts' ); // Clone of the raw data table for holding our aggregate chart data

// Path and URL definitions
define( 'KSTATS_PLUGIN_BASE',  WP_PLUGIN_DIR . '/kstats-reloaded/' );
define( 'KSTATS_PLUGIN_URL',   plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ) . '/' );
define( 'KSTATS_PLUGIN_MEDIA', KSTATS_PLUGIN_URL . 'media/' );

define( 'KSTATS_ADMIN_BASE',     KSTATS_PLUGIN_BASE . 'admin/' );
define( 'KSTATS_DEF_BASE',       KSTATS_PLUGIN_BASE . 'definitions/' );
define( 'KSTATS_LIB_BASE',       KSTATS_PLUGIN_BASE . 'lib/' );
define( 'KSTATS_UTILITIES_BASE', KSTATS_PLUGIN_BASE . 'utilities/' );

// Numerical Chart Codes for the Top Charts
define( 'KSTATS_CHARTS_AGENTS',    10 );
define( 'KSTATS_CHARTS_REFERRERS', 20 );
define( 'KSTATS_CHARTS_SEARCHES',  30 );
define( 'KSTATS_CHARTS_SPIDERS',   40 );

// kStats definition files
define( 'KSTATS_INI_CACHE', KSTATS_DEF_BASE . 'cache.ini' );
define( 'KSTATS_INI_UA', KSTATS_DEF_BASE . 'agents.ini' );
define( 'KSTATS_INI_QUERY', KSTATS_DEF_BASE . 'query.ini' );

define( 'KSTATS_DEBUG', 0 );

if ( get_option( 'kstats_uninstalled' ) )
	define( 'KSTATS_DISABLED', 1 );


// This lets us change the version information for testing purposes
if ( defined( 'KSTATS_DEBUG' ) && KSTATS_DEBUG == TRUE ) {

	if ( isset( $_POST['kstats_debug'], $_POST['kstats_version'] ) )
		update_option( 'kstats_version', $_POST['kstats_version'] );

	$wpdb->show_errors();

}


/**
 * kStats Required Files
 */

_kstats_load_library( KSTATS_LIB_BASE );
_kstats_load_library( KSTATS_ADMIN_BASE );

$kstats = new kstats_reloaded;


/**
 * Add and manage all hooks
 */

if ( $kstats->upgrade === FALSE  && ! defined( 'KSTATS_DISABLED' ) ) {

	add_action( 'kstats_aggregate_hook', 'kstats_cleanup_handler' );
	add_action( 'send_headers', 'kstats_collector' ); // Collect them hits
}

// views.php
add_action( 'wp_dashboard_setup', 'kstats_add_dashboard_widget' );
add_action( 'admin_menu', 'kstats_add_pages' );

// class.kstats.php
if ( ! isset( $_POST['kstats_upgrade'] ) )
	add_action( 'admin_init', array( $kstats, 'init_options' ) ); // Init our options/settings

// widgets.php
add_action( 'widgets_init', 'kstats_load_widgets' );



/**
 * Check for any specific requirements of operation
 */

$ini_notice  = __( "Your kStats INI files aren't currently writable.", 'kstats-reloaded' ) . '<br />';
$ini_notice .= '<br />' . sprintf( __( 'Please make sure that all %1$s files in your definitions directory %2$s are writable (chmod 0777) before continuing.', 'kstats-reloaded' ), '<code>.ini</code>', '<code>' . KSTATS_DEF_BASE . '</code>' );

if ( ! is_writable( KSTATS_INI_CACHE ) && ! chmod( KSTATS_INI_CACHE, 0777 ) ) {
	$kstats->set_notice( 'definitions', $ini_notice );
}

if ( ! is_writable( KSTATS_INI_UA ) && ! chmod( KSTATS_INI_UA, 0777 ) ) {
	$kstats->set_notice( 'definitions', $ini_notice );
}


/**
 * Responsible for parsing and loading a library of files
 *
 * @since 0.5.0
 * @category Main
 * @subcategory Setup
 */

function _kstats_load_library( $library_folder = '' ) {

	if ( empty( $library_folder ) || ! file_exists( $library_folder ) )
		return FALSE;

	$library_files = array();

	if ( $library_dir = opendir( $library_folder ) ) {

		while ( ( $library_file = readdir( $library_dir ) ) !== FALSE ) {

			if ( substr( $library_file, 0, 1 ) == '.' || substr( $library_file, 0, 1 ) == '_' )
				continue;

			if ( is_dir( $library_folder . $library_file ) ) {

				if ( $library_subdir = opendir( $library_folder . $library_file ) ) {

					while ( ($library_subfile = readdir( $library_subdir ) ) !== false ) {

						if ( substr( $library_file, 0, 1 ) == '.' || substr( $library_file, 0, 1 ) == '_' )
							continue;

						if ( substr( $library_subfile, -4 ) == '.php' )
							$library_files[] = $library_file . '/' . $library_subfile;

					}

					closedir( $library_subdir );

				}

			}
			else {

				if ( substr($library_file, -4) == '.php' )
					$library_files[] = $library_file;

			}


		}

		closedir( $library_dir );

	}

	if ( empty( $library_files ) )
		return;

	sort( $library_files );

	foreach ( $library_files as $file )
		require_once( $library_folder . $file );

}
