<?php

/**
 * Update the locally cached INI files used by the Trap methods
 *
 * @package kStatsReloaded
 * @subpackage Utilities
 */

/**
 * @ignore
 */

if ( ! defined( 'KSTATS_VERSION' ) ) die( 'Aaaaaaaaaarrrggghhh!' );

	// Display the postbox
	kstats_postbox_ini_update();


/**
 * Display the INI update utility used for downloading new definitions files from
 * the remote server. At present files are downloaded directly from user-agent-string.info
 * however in the near future when other updates are made available, all files will
 * be downloaded directly from our servers.
 *
 * @since 0.6.0
 * @category Options
 */

function kstats_postbox_ini_update() {
	global $kstats;

	$output = '';

	// Check if a notice has been set, and append it
	$output .= $kstats->notices( 'definitions' );


	// Display a notification if update has been performed
	if ( isset( $_POST['kstats_update_def'] ) ) {

		if ( kstats_utility_ini_update() )
			$output .= '<p class="updated">' . __( 'Your INI files have been updated successfully.', 'kstats-reloaded' ) . '</p>';
		else
			$output .= '<p class="error">' . __( 'There was a problem downloading the new INI files. Please try again later.', 'kstats-reloaded' ) . '</p>';

	}
	else if ( isset( $_POST['kstats_update_optin'] ) ) {

		check_admin_referer( 'kstats_optin' );

		$optin = ( $_POST['kstats_optin'] == 1 ? 1 : 0 );
		update_option( 'kstats_optin', $optin );

	}

	$kstats_parser_cache = parse_ini_file( KSTATS_INI_CACHE );
	$last_updated = date( 'D, F jS \a\t g:ia', $kstats_parser_cache['lastupdate'] );

	$output .= '<form action="" method="post">';

	$output .= '<input type="hidden" name="kstats_admin" value="true" />';
	$output .= '<p class="aside">' . __( 'Last Update: ', 'kstats-reloaded' ) . '<strong>' . $last_updated . '</strong></p>';
	$output .= '<p>' . __( 'All INI files are downloaded from a remote server, allowing you to keep your definitions up to date without having to upgrade kStats itself.', 'kstats-reloaded' ) . '</p>';
	$output .= '<p class="kstats_submit center"><input type="submit" name="kstats_update_def" class="button-secondary" value="' . __( 'Update Definitions', 'kstats-reloaded' ) . '" /></p>';
	$output .= '</form>';


	$output .= '<form action="" method="post" style="margin-top: 2em">';
	$output .= wp_nonce_field( 'kstats_optin', '_wpnonce', 1, 0 );

	$output .= '<div class="aside"><p style="margin-top: 0"><input type="checkbox" name="kstats_optin" value="1" ' . checked( '1', get_option( 'kstats_optin' ), FALSE ) . ' /> ' . __( 'Help Find New Spiders', 'kstats-reloaded' ) . '</p>';
	$output .= '<p>' . sprintf( __( 'If you choose to opt in, kStats will automatically submit unknown user agents to the %s project in order to help more accurately identify Robots and Spiders crawling your web site.', 'kstats-reloaded' ), '<a href="http://user-agent-string.info/">user-agent-string.info</a>' ) . '</p>';
	$output .= '<p class="kstats_submit center"><input type="submit" name="kstats_update_optin" class="button-secondary" value="' . __( "Trap Spiders", 'kstats-reloaded' ) . '" /></p></div>';
	$output .= '</form>';



	kstats_build_postbox( 'update_def', __( 'Update Definitions', 'kstats-reloaded' ), $output );

}


/**
 * Updates the kStats definition INI files - currently only runs against the
 * user agents ini file from user-agent-string.info. In the future this should
 * also run an update against our own server to retrieve updated query.ini files
 *
 * @since 0.5.0
 * @ver 0.5.0
 *
 * @todo Should attempt to use cURL first, file_get_contents second
 */

function kstats_utility_ini_update() {

	$uas_ini_url = 'http://user-agent-string.info/rpc/get_data.php?key=free&format=ini';
	$uas_ver_url = $uas_ini_url . '&ver=y';

	if ( ini_get( 'allow_url_fopen' ) ) {

		$ctx = stream_context_create( array( 'http' => array( 'timeout' => 5 ) ) );

		$ver = @file_get_contents( $uas_ver_url, 0, $ctx );

		$cache_ini  = "[agents]\n";
		$cache_ini .= 'localversion = ' . $ver . "\n";
		$cache_ini .= 'lastupdate = ' . time() . "\n";

		@file_put_contents( KSTATS_INI_CACHE, $cache_ini );

		if ( $ini = @file_get_contents( $uas_ini_url, 0, $ctx ) )
			return ( file_put_contents( KSTATS_INI_UA, $ini ) ? TRUE : FALSE );

	}

	return FALSE;

}