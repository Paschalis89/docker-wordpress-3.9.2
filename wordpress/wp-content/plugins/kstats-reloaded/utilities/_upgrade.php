<?php

/**
 * The StatPress Reloaded conversion utility. Tested with SPR v1.5.21
 *
 * @package kStatsReloaded
 * @subpackage Utilities
 */

/**
 * @ignore
 */

if ( ! defined( 'KSTATS_VERSION' ) ) die( 'Aaaaaaaaaarrrggghhh!' );

if ( isset( $_POST['kstats_upgrade'] ) )
	kstats_upgrade();
else
	kstats_upgrade_display_form();



/**
 * Displays the form on the administration page of kStats that can
 * be used to convert raw data from StatPress Reloaded to work
 * with kStats
 *
 * @ver 0.4.2
 * @category Upgrade
 */

function kstats_upgrade_display_form() {

	$output  = '<form action="" method="post">';
	$output .= '<p style="font-size: 110%"><strong>' . __( 'kStats has detected that an upgrade and a little shoe shine is required to continue.', 'kstats-reloaded' ) . '</strong><br />';
	$output .= __( 'Please press the "Upgrade Now" button located below to run the upgrade script.', 'kstats-reloaded' ) . '</p>';
	$output .= '<p class="kstats_submit"><input type="submit" name="kstats_upgrade" class="button-secondary" value="' . __( 'Upgrade Now!', 'kstats-reloaded' ) . '" /></p>';
	$output .= '</form>';

	echo $output;

}


/**
 * Responsible for upgrading our database and adding any new options that
 * are required by the current version of kStats. Uses a switch to cascade
 * through each version, so the upgrade process can start from anywhere
 * and will end on the most recent.
 *
 * @ver 0.4.2
 * @category Upgrade
 */

function kstats_upgrade() {
	global $wpdb, $kstats;

	set_time_limit( 0 );
	ignore_user_abort( 1 );

	$charset = kstats_get_mysql_charset();
	$mysql5 = ( version_compare( mysql_get_server_info(), '5.0.3', '>' ) ? TRUE : FALSE );

	switch ( $kstats->upgrade ) {
		case '0.4.1':
			$query[] = sprintf( "SHOW COLUMNS FROM `%s`", KSTATS_TABLE_TOTALS );
			$results = $wpdb->get_results( end( $query ), ARRAY_A );

			$kstats_up_enum   = ( $results[0]['Type'] != "enum('totals','monthly','daily')" ) ? TRUE : FALSE;
			$kstats_up_fields = ( $results[2]['Field'] != 'visitors' ) ? TRUE : FALSE;

			if ( $kstats_up_enum === TRUE ) {
				$query[] = sprintf( "ALTER TABLE `%s` CHANGE `type` `type` ENUM( 'totals', 'monthly', 'daily' ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL", KSTATS_TABLE_TOTALS ); // bye Charts!
				$wpdb->query( end( $query ) );
			}

			if ( $kstats_up_fields === TRUE ) {
				// Change our column names to actually reference the data they're holding, in this way future columns can be appended, like Bounce Rate, etc
				$query[] = sprintf( "ALTER TABLE `%s` CHANGE `total` `visitors` INT(10) UNSIGNED NOT NULL, CHANGE `last` `pageviews` INT(10) UNSIGNED NOT NULL, CHANGE `this` `spiders` INT(10) UNSIGNED NOT NULL, CHANGE `yesterday` `feeds` INT(10) UNSIGNED NOT NULL", KSTATS_TABLE_TOTALS );
				$wpdb->query( end( $query ) );
			}

			$query[] = sprintf( "SELECT name, visitors, pageviews, spiders, feeds FROM `%s` WHERE type = ''", KSTATS_TABLE_TOTALS );
			$results = $wpdb->get_results( end( $query ), ARRAY_A );

			if ( ! is_null( $results ) ) {
				foreach ( $results as $row ) {
					if ( strlen( $row['name'] ) == 7 )
						$query_parts[] = sprintf( "( 'monthly', '%s', '%d', '%d', '%d', '%d' )", $row['name'], $row['visitors'], $row['pageviews'], $row['spiders'], $row['feeds'] );
					else if ( strlen( $row['name'] ) == 10 )
						$query_parts[] = sprintf( "( 'daily', '%s', '%d', '%d', '%d', '%d' )", $row['name'], $row['visitors'], $row['pageviews'], $row['spiders'], $row['feeds'] );
				}

				$query[] = sprintf( "REPLACE INTO `%s` VALUES %s", KSTATS_TABLE_TOTALS, implode( ', ', $query_parts ) );
				$wpdb->query( end( $query ) );

				$query[] = sprintf( "DELETE FROM `%s` WHERE type = ''", KSTATS_TABLE_TOTALS );
				$wpdb->query( end( $query ) );
			}

		case '0.4.2':

			delete_option( 'kstats_last_run' ); // Most people won't have this, but just in case

			// We're getting rid of these....
			$oldtable['agents']    = $wpdb->prefix . 'kstats_agents';
			$oldtable['referrers'] = $wpdb->prefix . 'kstats_referrers';
			$oldtable['searches']  = $wpdb->prefix . 'kstats_searches';
			$oldtable['spiders']   = $wpdb->prefix . 'kstats_spiders';

			// And updating these...
			$kstats_options = get_option( 'kstats_options' );

			// Mostly to change the naming conventions, but also to add the ignore list
			$new_options = array();
			$new_options['settings_database_months_to_keep']      = $kstats_options['settings_months'];
			$new_options['settings_database_days_to_keep']        = $kstats_options['settings_days'];
			$new_options['settings_database_recent_hits_to_keep'] = $kstats_options['settings_recent_hits'];
			$new_options['config_ignore_ignore_list']             = array( '127.0.0.1', '64.41.145.' );
			$new_options['config_ignore_ignore_list_2']           = array( '127.0.0.1', '64.41.145.' );
			$new_options['config_dashboard_display_widget']       = $kstats_options['settings_dashboard_widget'];
			$new_options['config_dashboard_days_to_show']         = $kstats_options['settings_dashboard_chart'];
			$new_options['config_overview_days_to_show']          = $kstats_options['settings_overview_chart'];
			$new_options['config_overview_recent_hits_to_show']   = 20;

			delete_option( 'kstats_options' );
			update_option( 'kstats_options', $new_options );


			$query[] = sprintf( 'CREATE TABLE IF NOT EXISTS `%1$s` (
								  `chart` int(10) unsigned DEFAULT NULL,
								  `timestamp` datetime NOT NULL,
								  `ip` int(10) unsigned NOT NULL, 
								  `url` %2$s NOT NULL,
								  `referrer` %2$s NOT NULL, 
								  `user_agent` %2$s DEFAULT NULL,
								  `os` varchar(255) DEFAULT NULL,
								  `browser` varchar(255) DEFAULT NULL,
								  `search_engine` varchar(255) DEFAULT NULL,
								  `search_terms` varchar(255) DEFAULT NULL,
								  `spider` varchar(255) DEFAULT NULL, ' . "
								  `feed` enum('','ATOM','COMMENT ATOM','COMMENT RSS','RDF','RSS','RSS2') DEFAULT NULL, " . '
								  `user` varchar(255) DEFAULT NULL,
								  `count` int(10) unsigned NOT NULL,
								  KEY `chart` (`chart`),
								  KEY `timestamp` (`timestamp`),
								  KEY `ip` (`ip`),
								  KEY `url` (`url`(255))
								) ENGINE=MyISAM %3$s ', KSTATS_TABLE_CHARTS, ( $mysql5 === TRUE ? 'varchar(1024)' : 'text' ), $charset );

			$wpdb->query( end( $query ) );

			// Because for some numnutz reason we were concatenating our Browser and OS in previous versions,
			// with the addition of the new user-agent-string.info parser, we'll pull all this data and rerun it through
			// the parser, getting more accurate OS and Browser information than before.
			$query[] = sprintf( "SELECT user_agent, count FROM `%s`", $oldtable['agents'] );
			$results = $wpdb->get_results( end( $query ), ARRAY_A );

			if ( ! is_null( $results ) ) {
				foreach ( $results as $row ) {
					list( $row['os'], $row['browser'] ) = explode( '|', kstats_trap_agent( $row['user_agent'] ) );
					$query_parts[] = sprintf( "( 10, '%s', '%s', '%s', '%d' )", $row['user_agent'], $row['os'], $row['browser'], $row['count'] );
				}
			}

			// Thanks to sevhs and their 9000 rows, it might be a plan to include this....
			kstats_cleanup_run_query( KSTATS_TABLE_CHARTS, array( 'chart', 'user_agent', 'os', 'browser', 'count' ), $query_parts, 600 );

			$query[] = sprintf( "DROP TABLE IF EXISTS `%s`", $oldtable['agents'] );
			$wpdb->query( end( $query ) );

			// Grab the rest of the data... 
			$query[] = sprintf( "INSERT INTO `%s` ( chart, referrer, url, count ) SELECT %d AS chart, referrer, viewed, count FROM `%s`", KSTATS_TABLE_CHARTS, KSTATS_CHARTS_REFERRERS, $oldtable['referrers'] );
			$wpdb->query( end( $query ) );
			$query[] = sprintf( "DROP TABLE IF EXISTS `%s`", $oldtable['referrers'] );
			$wpdb->query( end( $query ) );

			$query[] = sprintf( "INSERT INTO `%s` ( chart, search_terms, search_engine, url, referrer, count ) SELECT %d AS chart, terms, engine, viewed, referrer, count FROM `%s`", KSTATS_TABLE_CHARTS, KSTATS_CHARTS_SEARCHES, $oldtable['searches'] );
			$wpdb->query( end( $query ) );
			$query[] = sprintf( "DROP TABLE IF EXISTS `%s`", $oldtable['searches'] );
			$wpdb->query( end( $query ) );

			$query[] = sprintf( "INSERT INTO `%s` ( chart, user_agent, spider, url, count ) SELECT %d AS chart, user_agent, spider, url, count FROM `%s`", KSTATS_TABLE_CHARTS, KSTATS_CHARTS_SPIDERS, $oldtable['spiders'] );
			$wpdb->query( end( $query ) );
			$query[] = sprintf( "DROP TABLE IF EXISTS `%s`", $oldtable['spiders'] );
			$wpdb->query( end( $query ) );

		case '0.6.0':

			$query[] = sprintf( "ALTER TABLE `%s` DROP `id`", KSTATS_TABLE_RAW );
			$wpdb->query( end( $query ) );

			$query[] = sprintf( "UPDATE `%s` SET ip = INET_ATON( ip )", KSTATS_TABLE_RAW );
			$wpdb->query( end( $query ) );

		case '0.6.9':

			// We're going to do this manually instead of trusting dbDelta, since it ignores column order
			$query[] = sprintf( "SHOW COLUMNS FROM `%s` WHERE field = 'id'", KSTATS_TABLE_RAW ); // I know. It was a stupid idea taking it out.
			if ( is_null( $wpdb->get_var( end( $query ) ) ) ) {
				$query[] = sprintf( "ALTER TABLE `%s` ADD COLUMN id SERIAL FIRST", KSTATS_TABLE_RAW );
				$wpdb->query( end( $query ) );
			}

			// Please let this be the last time you change the darn format of these.
			$old_options = get_option( 'kstats_options' );

			add_action( 'admin_init', 'kstats_unregister_options' );
			delete_option( 'kstats_options' );

			$new_options = array( 'database_months_to_keep'      => ( isset( $old_options['settings_database_months_to_keep'] )    ? $old_options['settings_database_months_to_keep']    : 24 ),
								  'database_days_to_keep'        => ( isset( $old_options['settings_database_days_to_keep'] )      ? $old_options['settings_database_days_to_keep']      : 365 ),
								  'database_rows_to_keep'        => ( isset( $old_options['settings_database_rows_to_keep'] )      ? $old_options['settings_database_rows_to_keep']      : 2000 ),
								  'access_minimum_to_view'       => ( isset( $old_options['config_access_minimum_to_view'] )       ? $old_options['config_access_minimum_to_view']       : 'subscriber' ),
								  'access_minimum_to_edit'       => ( isset( $old_options['config_access_minimum_to_edit'] )       ? $old_options['config_access_minimum_to_edit']       : 'subscriber' ),
								  'dashboard_display_widget'     => ( isset( $old_options['config_dashboard_display_widget'] )     ? $old_options['config_dashboard_display_widget']     : 1 ),
								  'dashboard_days_to_show'       => ( isset( $old_options['config_dashboard_days_to_show'] )       ? $old_options['config_dashboard_days_to_show']       : 12 ),
								  'overview_days_to_show'        => ( isset( $old_options['config_overview_days_to_show'] )        ? $old_options['config_overview_days_to_show']        : 21 ),
								  'overview_recent_hits_to_show' => ( isset( $old_options['config_overview_recent_hits_to_show'] ) ? $old_options['config_overview_recent_hits_to_show'] : 20 ),
								  'ignore_ip_list'               => ( isset( $old_options['config_ignore_ignore_list'] )           ? $old_options['config_ignore_ignore_list']           : array( '127.0.0.1', '64.41.145.' ) )
								);

			add_option( 'kstats_options', $new_options );

		case '0.7.1':
			kstats_upgrade_encode();

		case 'final':
			kstats_create_tables();
			update_option( 'kstats_version', KSTATS_VERSION );
			break;

	} // switch

	echo '<p style="font-size: 110%"><strong>' . __( 'kStats has been upgraded!', 'kstats-reloaded' ) . '</strong></p>';
	echo '<p>' . __(  "You'll be able to sleep much better tonight knowing your statistics are safe and sound.", 'kstats-reloaded' ) . '</p>';

}

function kstats_unregister_options() {
			unregister_setting( 'kstats_admin_options', 'kstats_options', array( $this, 'validate_options' ) );
}

function kstats_upgrade_encode() {
	global $wpdb;

	// Clean up the charts table first.
	// Unencodes everything, strip it of slashes so we don't double up, esc and back in
	// Data going in should be escaped but normal, and not trusted on the way back out again.
	$query[] = sprintf( "SELECT url, referrer, search_engine, search_terms, count FROM `%s` WHERE chart = %d", KSTATS_TABLE_CHARTS, KSTATS_CHARTS_SEARCHES );
	$results = $wpdb->get_results( end( $query ), ARRAY_A );

	$query_parts = array();

	foreach ( $results as $key => $value ) {
		$value['url'] = htmlentities( stripslashes( $value['url'] ), ENT_QUOTES );
		$value['referrer'] = stripslashes( $value['referrer'] );
		$value['search_terms'] = html_entity_decode( urldecode( $value['search_terms'] ) );
		$query_parts[] = sprintf( "( '%d', '%s', '%s', '%s', '%s', '%d' )", KSTATS_CHARTS_SEARCHES, esc_sql( $value['url'] ), esc_sql( $value['referrer'] ), $value['search_engine'], esc_sql( htmlentities( $value['search_terms'], ENT_QUOTES ) ), $value['count'] );
	}

	$query[] = sprintf( "DELETE FROM `%s` WHERE chart = %d", KSTATS_TABLE_CHARTS, KSTATS_CHARTS_SEARCHES );
	$wpdb->query( end( $query ) );

	kstats_cleanup_large_query( KSTATS_TABLE_CHARTS, $query_parts, array( 'chart', 'url', 'referrer', 'search_engine', 'search_terms', 'count' ) );


	$query[] = sprintf( "SELECT url, referrer, count FROM `%s` WHERE chart = %d", KSTATS_TABLE_CHARTS, KSTATS_CHARTS_REFERRERS );
	$results = $wpdb->get_results( end( $query ), ARRAY_A );

	$query_parts = array();

	foreach ( $results as $key => $value ) {
		$value['url'] = htmlentities( stripslashes( $value['url'] ), ENT_QUOTES );
		$value['referrer'] = stripslashes( $value['referrer'] );
		$query_parts[] = sprintf( "( '%d', '%s', '%s', '%d' )", KSTATS_CHARTS_REFERRERS, esc_sql( $value['url'] ), esc_sql( $value['referrer'] ), $value['count'] );
	}

	$query[] = sprintf( "DELETE FROM `%s` WHERE chart = %d", KSTATS_TABLE_CHARTS, KSTATS_CHARTS_REFERRERS );
	$wpdb->query( end( $query ) );

	kstats_cleanup_large_query( KSTATS_TABLE_CHARTS, $query_parts, array( 'chart', 'url', 'referrer', 'count' ) );


	// Now lets run cleanup on the raw table.
	$query[] = sprintf( "DROP TABLE IF EXISTS `%s`", KSTATS_TABLE_RAW . '_temp' ); // Won't be necessary later, will use a temporary table
	$wpdb->query( end( $query ) );

	$query[] = sprintf( "CREATE TABLE `%s` SELECT id, search_terms FROM `%s` WHERE search_terms <> ''", KSTATS_TABLE_RAW . '_temp', KSTATS_TABLE_RAW );
	$wpdb->query( end( $query ) );

	$query[] = sprintf( "SELECT id, search_terms FROM `%s`", KSTATS_TABLE_RAW . '_temp' );
	$results = $wpdb->get_results( end( $query ), ARRAY_A );

	$query_parts = array();

	foreach ( $results as $key => $value ) {
		$value['search_terms'] = stripslashes( html_entity_decode( urldecode( $value['search_terms'] ) ) );
		$query_parts[] = sprintf( "( '%d', '%s' )", $value['id'], esc_sql( htmlentities( $value['search_terms'], ENT_QUOTES ) ) );
	}

	$query[] = sprintf( "TRUNCATE TABLE `%s`", KSTATS_TABLE_RAW . '_temp' );
	$wpdb->query( end( $query ) );

	$query[] = sprintf( "INSERT INTO `%s` VALUES %s", KSTATS_TABLE_RAW . '_temp', implode( ', ', $query_parts ) );
	$wpdb->query( end( $query ) );

	$query[] = sprintf( 'UPDATE `%1$s`, `%2$s` SET %1$s.search_terms = %2$s.search_terms WHERE %1$s.id = %2$s.id', KSTATS_TABLE_RAW, KSTATS_TABLE_RAW . '_temp' );
	$wpdb->query( end( $query ) );



	$query[] = sprintf( "DROP TABLE IF EXISTS `%s`", KSTATS_TABLE_RAW . '_temp' ); // Won't be necessary later, will use a temporary table
	$wpdb->query( end( $query ) );

	$query[] = sprintf( "CREATE TABLE `%s` SELECT id, url FROM `%s` WHERE url <> ''", KSTATS_TABLE_RAW . '_temp', KSTATS_TABLE_RAW );
	$wpdb->query( end( $query ) );

	$query[] = sprintf( "SELECT id, url FROM `%s`", KSTATS_TABLE_RAW . '_temp' );
	$results = $wpdb->get_results( end( $query ), ARRAY_A );

	$query_parts = array();

	foreach ( $results as $key => $value ) {
		$value['url'] = htmlentities( stripslashes( $value['url'] ), ENT_QUOTES );
		$query_parts[] = sprintf( "( '%d', '%s' )", $value['id'], esc_sql( $value['url'] ) );
	}

	$query[] = sprintf( "TRUNCATE TABLE `%s`", KSTATS_TABLE_RAW . '_temp' );
	$wpdb->query( end( $query ) );

	kstats_cleanup_large_query( KSTATS_TABLE_RAW . '_temp', $query_parts );

	$query[] = sprintf( 'UPDATE `%1$s`, `%2$s` SET %1$s.url = %2$s.url WHERE %1$s.id = %2$s.id', KSTATS_TABLE_RAW, KSTATS_TABLE_RAW . '_temp' );
	$wpdb->query( end( $query ) );

}