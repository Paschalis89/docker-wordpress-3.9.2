<?php /*
Plugin Name: kStats Reloaded
Plugin URI: http://mark.watero.us/kstats-reloaded/
Description: kStats Reloaded is one of the fastest real-time statistics plugins for Wordpress. See who's on your site, where they came from, and where they're going.
Version: 0.7.4
Author: Mark Waterous
Author URI: http://mark.watero.us/
*/

/**
 * Update the locally cached INI files used by the Trap methods
 *
 * @package kStatsReloaded
 * @subpackage Core
 */

global $wpdb;

define( 'KSTATS_VERSION', '0.7.4' );

register_activation_hook( __FILE__, 'kstats_activate' );
register_deactivation_hook( __FILE__, 'kstats_deactivate' );
register_uninstall_hook( __FILE__, 'kstats_uninstall' );


if ( isset( $_POST['kstats_uninstall'], $_POST['kstats_uninstall_confirm'] ) ) {
	kstats_uninstall();
	update_option( 'kstats_uninstalled', 1 );
}

require_once( 'kstats-config.php' );


/**
 * kStats Reloaded in a nutshell. Without this, nothing else matters.
 *
 * @since 0.1.0
 * @Category Main
 */
 
function kstats_collector() {
	global $wpdb, $kstats, $current_user;

	// Try to record stats regardless of complete page loads
	ignore_user_abort( 1 );

	get_currentuserinfo();

	// Don't record statistics for logged in administrators.
	/**	@todo This should be made configurable on the options page. Some blogs may have a lot of administrators and the owner could want to track them to? */
	if ( $current_user->ID == 1 || current_user_can( 'create_users' ) )
		return;

	
	$arr = array();

	$arr['ip'] = kstats_trap_ip();

	// Check the IP against our ignore list, exit if matched
	if ( kstats_ignore_ip( $arr['ip'] ) )
		return;

	$arr['url'] = kstats_trap_url(); // Request URI

	if ( strpos( $arr['url'], 'robots.txt' ) !== FALSE && $kstats->config['misc']['uas_opt_in'] && isset( $_SERVER['HTTP_USER_AGENT'] ) )
		kstats_trigger_async( 'http://user-agent-string.info:80/rpc/botdetect.php', array( 'ua' => $_SERVER['HTTP_USER_AGENT'], 'adr' => $_SERVER['REMOTE_ADDR'] ), 1, 'GET' );

	// Don't collect stats for these areas of the site
	if ( preg_match( '~(robots\.txt|wp-content/(mu-)?(plugins|themes)|wp-admin)~', $arr['url'] ) )
		return;

	// File extensions that we want to ignore
	$arr['ext'] = pathinfo( $arr['url'], PATHINFO_EXTENSION );
	if ( ! empty( $arr['ext'] ) && preg_match( '~ico|css|js|jpe?g|png|gif~', $arr['ext'] ) )
		return;


	// Set variables
	$arr['referrer']   = ( isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], $kstats->blog_url ) === FALSE ) ? htmlentities( $_SERVER['HTTP_REFERER'], ENT_QUOTES ) : '';
	$arr['user_agent'] = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? htmlentities( $_SERVER['HTTP_USER_AGENT'], ENT_QUOTES ) : '';


	// Default
	$arr['spider'] = $arr['os'] = $arr['browser'] = $arr['search_engine'] = $arr['search_terms'] = $arr['feed'] = FALSE;

	$arr['spider'] = kstats_trap_spider( $arr['user_agent'] );
         

	// Not a spider? Who is it? TELL ME.
	if ( $arr['spider'] === FALSE ) {
		$arr['feed']    = kstats_trap_feed( $arr['url'] );
        list( $arr['os'], $arr['browser'] ) = kstats_trap_agent( $arr['user_agent'] );
		list( $arr['search_engine'], $arr['search_terms'] ) = kstats_trap_search_terms( $arr['referrer'] );
	}


	$query  = sprintf( "INSERT /*! HIGH_PRIORITY */ INTO `%s` ( timestamp, ip, url, user_agent, referrer, spider, search_engine, search_terms, os, browser, feed, user ) ", KSTATS_TABLE_RAW );
	$query .= sprintf( "VALUES ( '%s', INET_ATON( '%s' ), '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
								date( 'Y-m-d H:i:s', $kstats->datetime->timestamp ),
								$arr['ip'],
								esc_sql( $arr['url'] ), // should be escaped when trapped
								esc_sql( $arr['user_agent'] ),
								esc_sql( $arr['referrer'] ),
								$arr['spider'],
								$arr['search_engine'],
								esc_sql( $arr['search_terms'] ),
								$arr['os'],
								$arr['browser'],
								$arr['feed'],
								$current_user->user_login ); // Data that isn't escaped is trusted as it's generated internally

	$wpdb->query( $query ); // RUN IT!

}


/**
 * The activation process, schedules the nightly cron event and creates the database
 * tables we'll be using to store our data. All tables are MyIsam for speed with the
 * single exception of InnoDB on our raw data table, as this should be the only table
 * we need to make use of row level locking on.
 *
 * @ver 0.5.0
 * @category Main
 * @subcategory Setup
 * @return void
 */

function kstats_activate() {
	global $wpdb, $kstats;

    if ( version_compare( phpversion(), '5.0', '<' ) )
        wp_die( '<strong>We apologize, but kStats Reloaded requires PHP 5 or above for normal operation.</strong><br />PHP 4 is outdated <a href="http://www.php.net/archive/2007.php#2007-07-13-1">by over 2 years now</a>, and you should contact your host and request that they upgrade your server immediately.' );

	if ( get_option( 'kstats_uninstalled' ) )
		delete_option( 'kstats_uninstalled' );

	if ( ! get_option( 'kstats_version' ) )
		update_option( 'kstats_version', KSTATS_VERSION );

	$cron = strtotime( date( 'Y/m/d', strtotime( '+1 day', $kstats->datetime->timestamp ) ) . ' 12:01am' );

	wp_schedule_event( $cron, 'daily', 'kstats_aggregate_hook' );

	$new_options = array( 'database_months_to_keep'      => 24,
						  'database_days_to_keep'        => 365,
						  'database_rows_to_keep'        => 2000,
						  'access_minimum_to_view'       => 'subscriber',
						  'access_minimum_to_edit'       => 'subscriber',
						  'dashboard_display_widget'     => 1,
						  'dashboard_days_to_show'       => 12,
						  'overview_days_to_show'        => 21,
						  'overview_recent_hits_to_show' => 20,
						  'ignore_ip_list'           => array( '127.0.0.1', '64.41.145.' )
						);

	update_option( 'kstats_options', $new_options );

	$query   = sprintf( "SHOW TABLES LIKE '%skstats%%'", $wpdb->prefix );
	$results = $wpdb->get_results( $query, ARRAY_A );

	// We run this here if the tables don't exist, OR if the ugprade isn't going to run it itself.
	if ( count( $results ) != 3 || $kstats->upgrade === FALSE )
		kstats_create_tables();

}


/**
 * Handles the creation of our database tables, used by both the activation
 * process and upgrade process.
 *
 * @since 0.6.0
 * @category Main
 * @subcategory Setup
 */

function kstats_create_tables() {
	global $wpdb;

	$charset = kstats_get_mysql_charset();

	if ( ! function_exists( 'dbDelta' ) )
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// Determine our MySQL version, so we know if we can use varchar or if we have to use text
	// columns on pre 5.0.3 - varchar's should be as small as humanly possible, while still being able
	// to accept the possibility of extra long strings...

	$mysql5 = ( version_compare( mysql_get_server_info(), '5.0.3', '>' ) ? TRUE : FALSE );

	$tables['raw'] = "CREATE TABLE IF NOT EXISTS `" . KSTATS_TABLE_RAW . "` (
						  `id` SERIAL,
						  `timestamp` datetime NOT NULL,
						  `ip` int(10) unsigned NOT NULL,
						  `url` " . ( $mysql5 === TRUE ? 'varchar(1024)' : 'text' ) . " NOT NULL,
						  `referrer` " . ( $mysql5 === TRUE ? 'varchar(1024)' : 'text' ) . " NOT NULL,
						  `user_agent` " . ( $mysql5 === TRUE ? 'varchar(1024)' : 'text' ) . " DEFAULT NULL,
						  `os` varchar(255) DEFAULT NULL,
						  `browser` varchar(255) DEFAULT NULL,
						  `search_engine` varchar(255) DEFAULT NULL,
						  `search_terms` varchar(255) DEFAULT NULL,
						  `spider` varchar(255) DEFAULT NULL,
						  `feed` enum('','ATOM','COMMENT ATOM','COMMENT RSS','RDF','RSS','RSS2')DEFAULT NULL,
						  `user` varchar(255) DEFAULT NULL,
						  `preserved` enum('1') DEFAULT NULL,
						  KEY `timestamp` (`timestamp`),
						  KEY `ip` (`ip`),
						  KEY `url` (`url`(255))
						) ENGINE=InnoDB " . $charset;

	/** @todo Should we be using meta tables to store os, browser, etc? This would reduce our table size due to the amount of empty fields... */

	$tables['charts'] = "CREATE TABLE IF NOT EXISTS `" . KSTATS_TABLE_CHARTS . "` (
						  `chart` int(10) unsigned DEFAULT NULL,
						  `timestamp` datetime DEFAULT NULL,
						  `ip` int(10) unsigned DEFAULT NULL,
						  `url` " . ( $mysql5 === TRUE ? 'varchar(1024)' : 'text' ) . " DEFAULT NULL,
						  `referrer` " . ( $mysql5 === TRUE ? 'varchar(1024)' : 'text' ) . " DEFAULT NULL,
						  `user_agent` " . ( $mysql5 === TRUE ? 'varchar(1024)' : 'text' ) . " DEFAULT NULL,
						  `os` varchar(255) DEFAULT NULL,
						  `browser` varchar(255) DEFAULT NULL,
						  `search_engine` varchar(255) DEFAULT NULL,
						  `search_terms` varchar(255) DEFAULT NULL,
						  `spider` varchar(255) DEFAULT NULL,
						  `feed` enum('','ATOM','COMMENT ATOM','COMMENT RSS','RDF','RSS','RSS2') DEFAULT NULL,
						  `user` varchar(255) DEFAULT NULL,
						  `count` int(10) unsigned NOT NULL,
						  KEY `chart` (`chart`),
						  KEY `timestamp` (`timestamp`),
						  KEY `ip` (`ip`),
						  KEY `url` (`url`(255))
						) ENGINE=MyISAM " . $charset;


	$tables['totals'] = "CREATE TABLE IF NOT EXISTS `" . KSTATS_TABLE_TOTALS . "` (
						   `type` enum('totals','monthly','daily') NOT NULL,
						   `name` varchar(12) NOT NULL,
						   `visitors` int(10) unsigned NOT NULL,
						   `pageviews` int(10) unsigned NOT NULL,
						   `spiders` int(10) unsigned NOT NULL,
						   `feeds` int(10) unsigned NOT NULL,
						   PRIMARY KEY `name` (`name`),
						   KEY `type` (`type`)
						 ) ENGINE=MyISAM " . $charset;

	foreach ( $tables as $table )
		dbDelta( $table );

}


/**
 * Determines the character set and collation to use in our database
 * based on the settings provided by Wordpress. Defaults to utf8 if
 * Wordpress data isn't defined.
 *
 * @since 0.6.0
 * @category Main
 * @subcategory Setup
 * @return string Formatted string with character collation for MySQL
 */

function kstats_get_mysql_charset() {
	global $wpdb;

	if ( defined('DB_CHARSET') && ! empty( $wpdb->charset ) ) {

		$charset = 'DEFAULT CHARACTER SET ' . $wpdb->charset;

		if ( ! empty( $wpdb->collate ) )
			$charset .= ' COLLATE ' . $wpdb->collate;

	}
	else {
		$charset = 'DEFAULT CHARACTER SET utf8 COLLATE utf8_bin';
	}

	return $charset;
}


/**
 * Deactivation clears the scheduled cron hook. You must uninstall the plugin
 * via the Plugins page or via the plugin Options page to remove all data.
 *
 * @since 0.6.0
 * @category Main
 * @subcategory Setup
 */

function kstats_deactivate() {
	wp_clear_scheduled_hook( 'kstats_aggregate_hook' );
	delete_option( 'kstats_async' );
}


/**
 * On uninstall, kStats will unschedule the cron event, remove all options, and
 * drop any existing database tables.
 *
 * @ver 0.6.0
 * @category Main
 * @subcategory Setup
 */

function kstats_uninstall() {
	global $wpdb;

	wp_clear_scheduled_hook( 'kstats_aggregate_hook' );

	delete_option( 'kstats_version' );
	delete_option( 'kstats_options' );
	delete_option( 'kstats_async' );
	if ( get_option( 'widget_kstats' ) )
		delete_option( 'widget_kstats' );

	$query   = sprintf( "SHOW TABLES LIKE '%skstats%%'", $wpdb->prefix );
	$results = $wpdb->get_results( $query, ARRAY_A );

	$tables = array();

	foreach ( $results as $arr ) {
		$tmp = array_values( $arr );
		array_push( $tables, $tmp[0] );
	}

	foreach ( $tables as $table )
		$wpdb->query( "DROP TABLE IF EXISTS `" . $table . "`" );

}
