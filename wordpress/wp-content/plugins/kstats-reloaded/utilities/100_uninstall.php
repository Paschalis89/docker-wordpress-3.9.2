<?php

/**
 * kStats uninstall utility. Uses the Setup function kstats_uninstall.
 *
 * @package kStatsReloaded
 * @subpackage Utilities
 */

/**
 * @ignore
 */

if ( ! defined( 'KSTATS_VERSION' ) ) die( 'Aaaaaaaaaarrrggghhh!' );

	/** @todo Utilities should probably run themselves based on their filenames? */
	kstats_postbox_uninstall();


/**
 * Displays the uninstall utility on the Options page.
 * Uninstall is handled in the main plugin file, so that after completing
 * this process the plugin will be disabled.
 *
 * @since 0.6.0
 * @category Options
 */

function kstats_postbox_uninstall() {
	global $wpdb;

	$output  = '<form action="" method="post">';
	$output .= '<input type="hidden" name="kstats_admin" value="true" />'; // What is this for again?

	if ( isset( $_POST['kstats_uninstall'] ) && ! isset( $_POST['kstats_uninstall_confirm'] ) ) {
		$output .= '<p class="error">' . __( 'You must check the confirm box before continuing.', 'kstats-reloaded' ) . '</p>';
	}

	$output .= '<p>' . __( 'kStats requires some additional options and database tables in order to function. These features are not removed on deactivated to ensure that no data is lost unintentionally.', 'kstats-reloaded' ) . '</p>';
	$output .= '<p>' . __( 'If you are planning on removing kStats by hand instead of using the automatic delete feature, please be sure to run this uninstall utility first to remove all additional data.', 'kstats-reloaded' ) . '</p>';
	$output .= '<p class="aside"><input type="checkbox" name="kstats_uninstall_confirm" value="1" /> ' . __( 'Please confirm before proceeding.', 'kstats-reloaded' ) . '</p>';
	$output .= '<p class="kstats_submit center"><input type="submit" name="kstats_uninstall" class="button-secondary" value="' . __( 'Uninstall', 'kstats-reloaded' ) . '" /></p>';

	$output .= '</form>';

	kstats_build_postbox( 'statpress', __( 'Uninstall kStats', 'kstats-reloaded' ), $output );

}
