<?php


/**
 * Responsible for the primary view on the Overview page. Shows the totals
 * in dialogs at the top (format: all-time/today), followed by a numerical
 * chart displaying the last five months, yesterday and today. 
 *
 * @since 0.1.0
 * @category Views
 * @subcategory Overview
 * @param array $totals  An array holding the all-time totals
 * @param array $monthly An array holding the monthly totals
 * @param array $daily   An array holding the daily totals
 *
 * @todo Adds jQuery tabs, so we can display monthly, weekly and daily charts.
 */

function kstats_display_overview( $totals, $monthly, $daily ) {
	global $kstats;

	if ( ! is_array( $totals ) || ! is_array( $monthly ) || ! is_array( $daily ) )
		return;

	$display['months'] = array_reverse( $kstats->datetime->get_time_last_x_months( 4 ) );

?>

	<table class="kstatsfat totals" cellspacing="0">
    <tr><td style="border-left: 0;"><?php echo number_format( $totals['visitors'] ) . ' / ' . $daily[$kstats->datetime->today]['visitors']; ?></td>
        <td><?php echo number_format( $totals['pageviews'] ) . ' / ' . $daily[$kstats->datetime->today]['pageviews']; ?></td>
        <td><?php echo number_format( $totals['spiders'] ) . ' / ' . $daily[$kstats->datetime->today]['spiders']; ?></td>
        <td style="border-right: 0;"><?php echo number_format( $totals['feeds'] ) . ' / ' . $daily[$kstats->datetime->today]['feeds']; ?></td>
	</tr>
    <tr><th scope="col" style="border-left: 0;">Visitors</th>
        <th scope="col">Pageviews</th>
        <th scope="col">Spiders</th>
        <th scope="col" style="border-right: 0;">Feeds</th>
	</tr>

	</table>


	<table class="kstatsfat" cellspacing="0">
    <thead>
    <tr><th scope="col">&nbsp;</th>
	<?php for ( $i = 0; $i <= 4; $i++ ): ?>
    	<?php 	$th_style = '';
				$th_body = date ( 'M, Y', strtotime( $display['months'][$i] . '-01 00:00:00' ) );
				if ( $display['months'][$i] == $kstats->datetime->this_month ) {
					$th_style = ' style="color: #757575;"';
					$th_body  = 'This Month<span>' . $th_body . '</span>';
				}
		?>
        <th scope="col"<?php echo $th_style; ?>><?php echo $th_body; ?></th>
    <?php endfor; ?>
        <th scope="col" style="border-left: 1px solid #e3e3e3; padding-left: 20px;">Yesterday<span><?php echo date ( 'd M, Y', strtotime( $kstats->datetime->yesterday ) ); ?></span></th>
        <th scope="col" style="color: #757575;">Today<span><?php echo date ( 'd M, Y', strtotime( $kstats->datetime->today ) ); ?></span></th>
	</tr>
    </thead>

	<tbody>
	<tr><td><div class="boxes sc_visitors"></div> Visitors</td>
	<?php for ( $i = 0; $i <= 4; $i++ ): ?>
        <td><?php echo isset( $monthly[$display['months'][$i]]['visitors'] ) ? number_format( $monthly[$display['months'][$i]]['visitors'] ) : 0; ?></td>
    <?php endfor; ?>
        <td style="border-left: 1px solid #e3e3e3; padding-left: 20px;"><?php echo isset( $daily[$kstats->datetime->yesterday]['visitors'] ) ? number_format( $daily[$kstats->datetime->yesterday]['visitors'] ) : 0; ?></td>
        <td><?php echo isset( $daily[$kstats->datetime->today]['visitors'] ) ? number_format( $daily[$kstats->datetime->today]['visitors'] ) : 0; ?></td>
	</tr>

	<tr><td><div class="boxes sc_pageviews"></div> Page Views</td>
	<?php for ( $i = 0; $i <= 4; $i++ ): ?>
        <td><?php echo isset( $monthly[$display['months'][$i]]['pageviews'] ) ? number_format( $monthly[$display['months'][$i]]['pageviews'] ) : 0; ?></td>
    <?php endfor; ?>
        <td style="border-left: 1px solid #e3e3e3; padding-left: 20px;"><?php echo isset( $daily[$kstats->datetime->yesterday]['pageviews'] ) ? number_format( $daily[$kstats->datetime->yesterday]['pageviews'] ) : 0; ?></td>
        <td><?php echo isset( $daily[$kstats->datetime->today]['pageviews'] ) ? number_format( $daily[$kstats->datetime->today]['pageviews'] ) : 0; ?></td>
	</tr>

	<tr><td><div class="boxes sc_spiders"></div> Spiders</td>
	<?php for ( $i = 0; $i <= 4; $i++ ): ?>
        <td><?php echo isset( $monthly[$display['months'][$i]]['spiders'] ) ? number_format( $monthly[$display['months'][$i]]['spiders'] ) : 0; ?></td>
    <?php endfor; ?>
        <td style="border-left: 1px solid #e3e3e3; padding-left: 20px;"><?php echo isset( $daily[$kstats->datetime->yesterday]['spiders'] ) ? number_format( $daily[$kstats->datetime->yesterday]['spiders'] ) : 0; ?></td>
        <td><?php echo isset( $daily[$kstats->datetime->today]['spiders'] ) ? number_format( $daily[$kstats->datetime->today]['spiders'] ) : 0; ?></td>
	</tr>

	<tr><td><div class="boxes sc_feeds"></div> Feeds</td>
	<?php for ( $i = 0; $i <= 4; $i++ ): ?>
        <td><?php echo isset( $monthly[$display['months'][$i]]['feeds'] ) ? number_format( $monthly[$display['months'][$i]]['feeds'] ) : 0; ?></td>
    <?php endfor; ?>
        <td style="border-left: 1px solid #e3e3e3; padding-left: 20px;"><?php echo isset( $daily[$kstats->datetime->yesterday]['feeds'] ) ? number_format( $daily[$kstats->datetime->yesterday]['feeds'] ) : 0; ?></td>
        <td><?php echo isset( $daily[$kstats->datetime->today]['feeds'] ) ? number_format( $daily[$kstats->datetime->today]['feeds'] ) : 0; ?></td>
	</tr>

    </tbody>
    </table>

<?php

}



/**
 * Just a wrapper for the pageviews postbox.
 *
 * @since 0.6.0
 * @category Views
 * @subcategory Overview
 */

function kstats_display_recent_pageviews() {
?>
	<div class="postbox-container" style="width: 100%;">
	<div class="metabox-holder">	
	<div class="meta-box-sortables">

		<?php kstats_postbox_recent_pageviews(); ?>

	</div></div>
	</div> <!-- .postbox-container -->
<?php
}


/**
 * Display the recent pageviews in a postbox on the main overview page.
 * Currently includes rudimentary additional features, such as the ability
 * to group results by page or IP, and search by IP.
 *
 * @since 0.6.0
 * @category Views
 * @subcategory Overview
 *
 * @todo Add clickable IP's showing WHOIS and Geolocation
 */

function kstats_postbox_recent_pageviews() {
	global $wpdb, $kstats;

	$group_by  = '';
	$col_count = '';
	$output    = '';

	if ( isset( $_GET['by'] ) ) {
		$group_by  = ( $_GET['by'] == 'page' ? 'url' : 'ip' );
		$col_count = 'COUNT( ' . $group_by . ' ) AS count, ';
		$group_by  = 'GROUP BY ' . $group_by;
	}


	if ( isset( $_GET['ip'] ) && kstats_validate_ip( $_GET['ip'], FALSE ) ) {
		$ip = esc_sql( $_GET['ip'] );
		$query[] = sprintf( "SELECT timestamp, INET_NTOA( ip ) AS ip, url, referrer, user_agent, search_terms, os, browser FROM `%s` WHERE INET_NTOA( ip ) LIKE '%%%s%%' ORDER BY timestamp DESC", KSTATS_TABLE_RAW, $ip );
	}
	else {
		$query[] = sprintf( "SELECT %s timestamp, INET_NTOA( ip ) AS ip, url, referrer, user_agent, search_terms, os, browser FROM `%s` WHERE feed = '' AND spider = '' %s ORDER BY timestamp DESC LIMIT %d", $col_count, KSTATS_TABLE_RAW, $group_by, $kstats->config['overview']['recent_hits_to_show'] );
	}

	$results = $wpdb->get_results( end( $query ), ARRAY_A );

	if ( isset( $ip ) )
		$result_count = count( $results );

	$output .= '<ul class="options">';

	if ( ! empty( $group_by ) )
		$output .= '<li><a href="?page=' . $_GET['page'] . '">' . __( 'View Results by Time', 'kstats-reloaded' ) . '</a></li>';

	if ( isset( $_GET['by'] ) && $_GET['ip'] != 'ip' )
		$output .= '<li><a href="?page=' . $_GET['page'] . '&by=ip">' . __( 'Group Results by IP', 'kstats-reloaded' ) . '</a></li>';

	if ( isset( $_GET['by'] ) && $_GET['by'] != 'page' )
		$output .= '<li><a href="?page=' . $_GET['page'] . '&by=page">' . __( 'Group Results by Page', 'kstats-reloaded' ) . '</a></li>';

	$output .= '</ul>';

	$output .= '<form action="" method="get" class="aside">';
	$output .= '<input type="hidden" name="page" value="kstats/statistics" />';
	$output .= '<label for="ip">' . __( 'Search by IP:', 'kstats-reloaded' ) . '</label> <input type="text" name="ip" value="' . ( isset( $_GET['ip'] ) ? $_GET['ip'] : '' ) . '" />';
	$output .= '<input type="submit" class="button-secondary" value="' . __( 'Search', 'kstats-reloaded' ) . '" />';

	if ( isset( $result_count ) ) {
		$output .= ' &nbsp; <span style="color: #aaa; font-style: italic;">' . sprintf( __( 'Search returned %d results...', 'kstats-reloaded' ), $result_count ) . '';
		$output .= ' &nbsp; <a href="?page=' . $_GET['page'] . '" style="color: #aaa; text-decoration: none; border-bottom: 1px dotted #bbb;">' . __( 'Clear results', 'kstats-reloaded' ) . '</a></span>';
	}
	else {

		if ( isset( $_GET['ip'] ) && ! kstats_validate_ip( $_GET['ip'] ) )
			$output .= ' &nbsp; <span style="color: #c00;">' . __( 'Invalid IP address. Please try your search again.', 'kstats-reloaded' ) . '</span>';
		else
			$output .= ' &nbsp; <span style="color: #aaa; font-style: italic;">' . __( 'IP fragments are acceptable to widen the focus of your search.', 'kstats-reloaded' ) . '</span>';

	}

	$output .= '</form>';

	$output .= '<table class="kstatsfat">';
	$output .= '<thead><tr>';
	$output .= '<th scope="col">' . __( 'Time', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col" style="padding-left: 27px;">' . __( 'IP', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col">';

	if ( isset( $_GET['by'] ) && $_GET['by'] == 'ip' )
		$output .= __( 'Last ', 'kstats-reloaded' );

	$output .= __( 'Page Viewed', 'kstats-reloaded' ) . '</th>';

	if ( ! empty( $group_by ) )
		$output .= '<th scope="col">' . __( 'Visits', 'kstats-reloaded' ) . '</th>';

	$output .= '<th scope="col">' . __( 'OS', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col">' . __( 'Browser', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col" style="width: 35px;">&nbsp;</th>';
	$output .= '</tr></thead>';

	$output .= '<tbody>';

	if ( is_null( $results ) && isset( $ip ) ) {
		$output .= '<tr><td colspan="5" style="padding: 4em; text-align: center;"><strong>' . __( "Sorry, your search didn't return any results.", 'kstats-reloaded' ) . '</strong></td></tr>';
	}
	else if ( is_null( $results ) || empty( $results ) ) {
    	$output .= '<tr><td colspan="5" style="padding: 4em; text-align: center;"><strong>' . __( "kStats hasn't had a chance to record any hits yet.", 'kstats-reloaded' );
		$output .= '</strong><br />' . __( 'It could be broken. Unless you just turned it on.', 'kstats-reloaded' );
		$output .= '<br /><span style="font-size: 9px;">' . __( "It's probably just broken.", 'kstats-reloaded' ) . '</span></td></tr>';
	}
	else {

		foreach ( $results as $the ) {
			$output .= '<tr><td style="font-size: 10px; color: #aaa;"><abbr title=' . date( 'g:ia', strtotime( $the['timestamp'] ) ) . '>' . date( 'H:i:s / m-d-Y', strtotime( $the['timestamp'] ) ) . '</abbr></td>';
			$output .= '<td style="font-size: 10px;"><img src="http://api.hostip.info/flag.php?ip=' . $the['ip'] . '" width="18" height="12" style="margin-right: 5px; position: relative; top: 2px; border: 1px solid #eee;">' . $the['ip'] . '</td>';
			$output .= '<td><a href="' . kstats_entities( $the['url'] ) . '">' . kstats_truncate( $the['url'], 30 ) . '</a></td>';

			if ( ! empty( $group_by ) )
				$output .= '<td>' . $the['count'] . '</td>';

			$output .= '<td style="font-size: 9px; color: #aaa;">' . ( empty( $the['os'] ) ? '<abbr title="' . kstats_entities( $the['user_agent'] ) . '">unknown</a>' : $the['os'] ) . '</td>';
			$output .= '<td style="font-size: 9px; color: #aaa;">' . ( empty( $the['browser'] ) ? '<abbr title="' . kstats_entities( $the['user_agent'] ) . '">unknown</a>' : $the['browser'] ) . '</td>';
			$output .= '<td>';
			if ( ! empty( $the['referrer'] ) )
				$output .= '<a href="' . $the['referrer'] . '"><img src="' . KSTATS_PLUGIN_MEDIA . 'recent_referrer.png" width="16" height="16" style="margin-right: 3px;" title="' . __( 'View Referrer: ', 'kstats-reloaded' ) . kstats_entities( $the['referrer'] ) . '" /></a>';
			else
				$output .= '<div style="display: block; width: 19px;"></div>';

			if ( ! empty( $the['search_terms'] ) )
				$output .= '<img src="' . KSTATS_PLUGIN_MEDIA . 'recent_search.png" width="16" height="16" title="' . __( 'Found Via: ', 'kstats-reloaded' ) . '\'' . kstats_entities( $the['search_terms'] ) . '\'" />';
			else
				$output .= '<div style="display: block; width: 16px;"></div>';

			$output .= '</td>';
			$output .= '</tr>';
		}

	}

	$output .= '</tbody></table>';

	kstats_build_postbox( 'recent_pageviews', __( 'Recent Pageviews', 'kstats-reloaded' ), $output );

}
