<?php
/**
 * Update the locally cached INI files used by the Trap methods
 *
 * @package kStatsReloaded
 * @subpackage Views
 */

/**
 * Activate the dashboard pages, and append our styles and necessary scripts
 * only to these pages. Runs checks against the configuration settings to
 * allow access by other users to the stats or options. Blog owner (id 1)
 * automagically has access, but of course.
 *
 * @since 0.1.0
 * @category Views
 */

function kstats_add_pages() {
	global $kstats, $current_user;

	get_currentuserinfo();

	// Just to cover our butts
	$min_to_view = ( isset( $kstats->config['access']['minimum_to_view'] ) ? $kstats->access_levels[$kstats->config['access']['minimum_to_view']] : 'create_users' );
	$min_to_edit = ( isset( $kstats->config['access']['minimum_to_edit'] ) ? $kstats->access_levels[$kstats->config['access']['minimum_to_edit']] : 'create_users' );


	if ( $current_user->ID == 1 || current_user_can( $min_to_view ) ) {
		add_object_page( 'kStats Reloaded', 'kStats', $min_to_view, 'kstats/statistics', 'kstats_display', KSTATS_PLUGIN_URL . 'media/kstats.png' );

		$hook_overview = add_submenu_page( 'kstats/statistics', __('Blog Statistics - Overview', 'kstats-reloaded' ), __('Overview', 'kstats-reloaded' ), $min_to_view, 'kstats/statistics', 'kstats_display' );
			add_action( 'admin_print_styles-' . $hook_overview, 'kstats_print_styles' ); 
			add_action( 'admin_print_scripts-' . $hook_overview, 'kstats_print_scripts' ); 

		$hook_charts   = add_submenu_page( 'kstats/statistics', __('Blog Statistics - Charts', 'kstats-reloaded' ), __('Charts', 'kstats-reloaded' ), $min_to_view, 'kstats/charts', 'kstats_display' );
			add_action( 'admin_print_styles-' . $hook_charts, 'kstats_print_styles' ); 
			add_action( 'admin_print_scripts-' . $hook_charts, 'kstats_print_scripts' ); 

		if ( $current_user->ID == 1 || current_user_can( $min_to_edit ) ) {
			$hook_options  = add_submenu_page( 'kstats/statistics', __('Blog Statistics - Options', 'kstats-reloaded' ), __('Options', 'kstats-reloaded' ), $min_to_edit, 'kstats/options', 'kstats_display' );
				add_action( 'admin_print_styles-' . $hook_options, 'kstats_print_styles' ); 
				add_action( 'admin_print_scripts-' . $hook_options, 'kstats_print_scripts' ); 
		}

	}
	
}


/**
 * Apply our stylesheet and the Wordpress dashboard style sheets (for use
 * in displaying our postboxes)
 *
 * @since 0.1.0
 * @category Views
 */

function kstats_print_styles() {
	wp_enqueue_style( 'dashboard' );
	wp_enqueue_style( 'kstats', KSTATS_PLUGIN_URL . 'media/global.css', '', KSTATS_VERSION, 'all' );
}


/**
 * Append the Wordpress dashboard scripts to enable our postbox functionality.
 *
 * @since 0.6.0
 * @category Views
 */

function kstats_print_scripts() {
	wp_enqueue_script('dashboard');
}


/**
 * The primary view controller, this method is the only one used by kstats_add_pages
 * as it will determine which page we're looking for. If the plugin is in need of an
 * upgrade or has been disabled due to an uninstall, we'll block access from here
 * leaving the menu options intact. The majority of our debug information is also
 * displayed here.
 *
 * @since 0.4.0
 * @category Views
 */

function kstats_display () {
	global $wpdb, $kstats;

	$page = ( $kstats->upgrade !== FALSE ) ? '_upgrade' : $_GET['page'];

	if ( defined( 'KSTATS_DISABLED' ) )
		$page = '_disabled';


	if ( substr( $page, 0, 1 ) != '_' ) {
		$tabs  = '<style type="text/css">';
		$tabs .= '#kstats-menu { display: inline; position: relative; }';
		$tabs .= '#kstats-menu a, #kstats-menu a.link, #kstats-menu a:visited { text-decoration: none; z-index: 1; margin: 0 auto; padding: 0 6px 0 6px; height: 22px; line-height: 22px; font-size: 10px; background-repeat: no-repeat; background-position: right bottom; color: #555; text-shadow: #fff -1px 1px 0; }';
		$tabs .= '#kstats-menu a:hover { color: #222; }';
		$tabs .= '.kstats-menu-link { float: right; background: transparent url("/wp-admin/images/screen-options-left.gif") no-repeat 0 0; font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif; height: 22px; padding: 0; margin: 0 6px 0 0; text-decoration: none; text-align:center; -moz-border-radius-bottomright: 2px; -khtml-border-radius-bottomright: 2px; -webkit-border-bottom-right-radius: 2px; border-bottom-right-radius: 2px; -moz-border-radius-bottomleft: 2px; -khtml-border-radius-bottomleft: 2px; -webkit-border-bottom-left-radius: 2px; border-bottom-left-radius: 2px; }';
		$tabs .= '</style>';

		$tabs .= '<ul id="kstats-menu">';
		$tabs .= ( $page != 'kstats/options' )    ? '<li class="kstats-menu-link"><a href="?page=kstats/options">' . __( 'Options', 'kstats-reloaded' ) . '</a></li>' : '';
		$tabs .= ( $page != 'kstats/charts' )     ? '<li class="kstats-menu-link"><a href="?page=kstats/charts">' . __( 'Charts', 'kstats-reloaded' ) . '</a></li>' : '';
		$tabs .= ( $page != 'kstats/statistics' ) ? '<li class="kstats-menu-link"><a href="?page=kstats/statistics">' . __( 'Overview', 'kstats-reloaded' ) . '</a></li>' : '';
		$tabs .= '</ul><div style="clear: right;"></div>';

		echo $tabs;
	}


	// I thought this was supposed to be handled automatically, but may only on add_options_pages?
	if ( isset( $_GET['updated'] ) && $_GET['updated'] == TRUE )
		echo '<div class="updated" style="padding: 2px 4px;">' . __( 'Your options have been saved.', 'kstats-reloaded' ) . '</div>';


	// @todo The notices functionality of our kstats class is rudimentary at best right now. But it works...
	if ( isset( $kstats->notices ) && $page != '_upgrade' ) {
		foreach ( $kstats->notices as $notice ) 
			echo '<p class="error">' . $notice . '</p>';
	}

	$bgpx = ( $page == '_upgrade' ? '22px' : '0' );

	echo '<div class="wrap" style="background: transparent url(\'' . KSTATS_PLUGIN_MEDIA . 'kbg.png\') no-repeat 100% ' . $bgpx . ';">';
	echo ( $page != '_disabled' ? '<div id="kstats-icon" style="background: transparent url(\''. KSTATS_PLUGIN_MEDIA . 'k_icon.png\') no-repeat;" class="icon32"><br /></div>' : '' );


	if ( defined( 'KSTATS_DEBUG' ) && KSTATS_DEBUG == TRUE ) {

		echo '<div class="updated">';
		echo '<form action="" method="post">';
		echo '<label for="kstats_version" style="position: relative; top: -2px;">Current Version:</label> <input type="text" name="kstats_version" value="' . get_option( 'kstats_version' ) . '" />';
		echo '<input type="submit" class="button-secondary" name="kstats_debug" value="Change" />';
		echo '</form></div>';

		echo '<div class="updated" style="padding: 0.6em 1em; font-family: Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15px;">';
		echo 'Defined Version: <strong>' . KSTATS_VERSION . '</strong><br />';
		echo 'Current Timestamp: <strong>' . $kstats->datetime->timestamp . '</strong><br />';
		echo 'Current Time: <strong>' . date( 'Y-m-d g:ia', $kstats->datetime->timestamp ) . '</strong><br /><br />';
		echo '<strong><pre>' . print_r( get_option( 'kstats_options' ), TRUE ) . '</pre></strong>';
		echo '</div>';

	}


	switch ( $page ) {

		case '_disabled':
//			$output  = '<h2 style="margin-bottom: 1em;">' . __( 'kStats has been disabled', 'kstats-reloaded' ) . '</h2>';
			$output  = '<p style="margin-top: 2em; font-size: 110%"><strong>' . __( 'kStats has been disabled.', 'kstats-reloaded' ) . '</strong><br />';
			$output .= __( 'Your database tables or options may be corrupt, or you have performed an uninstall via the Options page.', 'kstats-reloaded' ) . '</p>';
			$output .= sprintf( __( 'In order for kStats to resume normal operation, you will have to deactivate and reactivate the plugin via the %sPlugins Page%s .', 'kstats-reloaded' ), '<a href="/wp-admin/plugins.php">', '</a>' ) . '</p>';
			echo $output;
			break;

		case '_upgrade':
		    echo '<h2 style="margin-bottom: 1em;">' . __( 'Upgrading kStats', 'kstats-reloaded' ) . '</h2>';
			require_once( KSTATS_UTILITIES_BASE . '_upgrade.php' );
			break;

		case 'kstats/statistics':
		    echo '<h2 style="margin-bottom: 1em;">' . sprintf( __( 'Statistics Overview for %s', 'kstats-reloaded' ), get_bloginfo( 'title' ) ) . '</h2>';
			extract( kstats_get_totals() );
   			kstats_display_overview( $totals, $monthly, $daily );

			list( $graph_data, $max ) = kstats_get_graph_data( $kstats->config['overview']['days_to_show'], $daily );
			kstats_display_bargraph( $graph_data, $max );
			kstats_display_recent_pageviews();
			break;

		case 'kstats/charts':
		    echo '<h2 style="margin-bottom: 1em;">' . __( 'Charts and Data', 'kstats-reloaded' ) . '</h2>';
			kstats_display_charts();
			break;

		case 'kstats/options':
		    echo '<h2 style="margin-bottom: 1em;">' . __( 'Options and Configuration', 'kstats-reloaded' ) . '</h2>';
			kstats_display_options();
			break;

	}


	if ( defined( 'KSTATS_DEBUG' ) && KSTATS_DEBUG == TRUE ) {

		echo '<div style="clear: both;"></div>';
		echo '<div class="error" style="padding: 0.6em 1em; font-family: Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15px;">';
		echo '<strong><pre>' . print_r( $kstats, TRUE ) . '</pre></strong>';
		echo '</div>';

	}

	echo '</div>'; // Close our .wrap

}


/**
 * Responsible for interpreting the data used by the charts and
 * generating the view for them. Determines the number of days to display
 * based on the array it's passed.
 *
 * @since 0.1.0
 * @category Views
 * @param array $daily An array of data used to build the graph
 * @param int   $max   Used to determine the scale used in our graph
 * @param bool  $ech   Whether to echo the output or return it
 */

function kstats_display_bargraph( $daily, $max, $ech = TRUE ) {
	global $kstats;

	if ( ! is_array( $daily ) || empty( $daily ) )
		return;

	$max = ( $max == 0 ) ? 1 : $max;

	// Determine which chart we're displaying and what the config for it is
	$days = ( $_GET['page'] == 'kstats/statistics' ? $kstats->config['overview']['days_to_show'] : $kstats->config['dashboard']['days_to_show'] );


    $column_width  = ( 95 / $days ) . '%';

	$first_day = $days - 1;

	$output = '';

	if ( defined( 'KSTATS_DEBUG' ) && KSTATS_DEBUG == TRUE ) {
		$output .= '<div class="updated">';
		$output .= '<p>Max: <strong>' . $max . '</strong></p>';
		$output .= '<p>Days: <strong>' . $days . '</strong></p>';
		$output .= '<strong><pre>' . print_r( $daily, TRUE ) . '</pre></strong>';
		$output .= '</div>';
	}

	$output .= '<table class="kstatsfat" style="margin-bottom: 3em;"><tr><td style="border: none;">';
	$output .= '<table width="99%" style="margin: 6px auto 0; border-collapse: collapse;"><tr>';

    while ( $days-- ) {

		$current = date( 'Y-m-d', $kstats->datetime->timestamp - 86400 * $days );

		$px_max = ( isset( $_GET['page'] ) && substr( $_GET['page'], 0, 6 ) == 'kstats' ) ? 200 : 140;
			
		if ( isset( $daily[$current] ) ) {
			$px_visitors  = round ( $daily[$current]['visitors'] * $px_max / $max );
			$px_pageviews = round ( $daily[$current]['pageviews'] * $px_max / $max );
			$px_spiders   = round ( $daily[$current]['spiders'] * $px_max / $max );
			$px_feeds     = round ( $daily[$current]['feeds'] * $px_max / $max );
	
			$px_white     = $px_max - $px_visitors - $px_pageviews - $px_spiders - $px_feeds;
		}
		else {
			$px_visitors = $px_pageviews = $px_spiders = $px_feeds = 0;
			$px_white = $px_max;
		}

		$prev_day = $kstats->datetime->start_of_week - 1;

		$output .= '<td width="' . $column_width . '" style="border: 1px solid #dadada; border-bottom: none;';
		if ( $kstats->datetime->start_of_week == date( 'w', $kstats->datetime->timestamp - 86400 * $days ) && $days != $first_day )
			$output .= ' border-left: 2px dashed #dadada; padding-left: 1px; ';
		else if ( $prev_day == date( 'w', $kstats->datetime->timestamp - 86400 * $days ) )
			$output .= ' padding-right: 1px; ';
		else
			$output .= '';
		$output .= '">';

		$output .= '<div style="float: left; height: 100%; width: 100%; line-height: 14px; font-size: 9px; font-family: Helvetica, Arial, sans-serif; text-align: center;">';
		$output .= '<div style="background: #fff url(' . KSTATS_PLUGIN_MEDIA . 'graph.png) repeat; width: 100%; height: ' . $px_white . 'px;"></div>';
		$output .= '<div class="bg_visitors"  style="width: 100%; border-top-right-radius: 3px; border-top-left-radius: 3px; -moz-border-radius-topright: 3px; -moz-border-radius-topleft: 3px; -webkit-border-top-right-radius: 3px; -webkit-border-top-left-radius: 3px; height: ' . $px_visitors . 'px;"  title="' . $daily[$current]['visitors'] . ' ' . __( 'visitors', 'kstats-reloaded' ) . '"></div>';
		$output .= '<div class="bg_pageviews" style="width: 100%; height: ' . $px_pageviews . 'px;" title="' . $daily[$current]['pageviews'] . ' ' . __( 'pageviews', 'kstats-reloaded' ) . '"></div>';
		$output .= '<div class="bg_spiders"   style="width: 100%; height: ' . $px_spiders . 'px;"   title="' . $daily[$current]['spiders'] . ' ' . __( 'spiders', 'kstats-reloaded' ) . '"></div>';
		$output .= '<div class="bg_feeds"     style="width: 100%; height: ' . $px_feeds . 'px;"     title="' . $daily[$current]['feeds'] . ' ' . __( 'feeds', 'kstats-reloaded' ) . '"></div>';
		$output .= '<div style="margin-bottom: 5px; background: #dadada; width: 100%; height: 3px;"></div>';
		$output .= date('M j', $kstats->datetime->timestamp - 86400 * $days) . '<br /><span style="color: #aaa;">' . date('D', $kstats->datetime->timestamp - 86400 * $days) . '</div></td>';

/*		$output .= '<div class="sc_visitors"  style="width: 100%; height: ' . $px_visitors . 'px;"  title="' . $daily[$current]['visitors'] . ' ' . __( 'visitors', 'kstats-reloaded' ) . '"><span style="color: #fff;">' . $daily[$current]['visitors'] . '</span></div>';
		$output .= '<div class="sc_pageviews" style="width: 100%; height: ' . $px_pageviews . 'px;" title="' . $daily[$current]['pageviews'] . ' ' . __( 'pageviews', 'kstats-reloaded' ) . '"><span style="color: #fff;">' . $daily[$current]['pageviews'] . '</span></div>';
		$output .= '<div class="sc_spiders"   style="width: 100%; height: ' . $px_spiders . 'px;"   title="' . $daily[$current]['spiders'] . ' ' . __( 'spiders', 'kstats-reloaded' ) . '"><span style="color: #fff;">' . $daily[$current]['spiders'] . '</span></div>';
		$output .= '<div class="sc_feeds"     style="width: 100%; height: ' . $px_feeds . 'px;"     title="' . $daily[$current]['feeds'] . ' ' . __( 'feeds', 'kstats-reloaded' ) . '"><span style="color: #fff;">' . $daily[$current]['feeds'] . '</span></div>';
*/
	}

	$output .= '</tr></table>';
	$output .= '</td></tr></table>';

	if ( $ech === TRUE )
		echo $output;
	else
		return $output;

}


/**
 * Used to build our Wordpress postboxes
 *
 * @since 0.6.0
 * @category Views
 */

function kstats_build_postbox( $id, $title, $content, $ech = TRUE ) {

	$output  = '<div id="kstats_' . $id . '" class="postbox">';
	$output .= '<div class="handlediv" title="Click to toggle"><br /></div>';
	$output .= '<h3 class="hndle"><span>' . $title . '</span></h3>';
	$output .= '<div class="inside">';
	$output .= $content;
	$output .= '</div></div>';

	if ( $ech === TRUE )
		echo $output;
	else
		return $output;

}
