<?php

function kstats_display_charts() {

	$views = array( 'searches', 'referrers', 'agents', 'spiders' );

	$view  = ( isset( $_GET['view'] ) && in_array( $_GET['view'], $views ) ? $_GET['view'] : 'all' );
	$limit = ( $view == 'all' ? 'LIMIT 20' : '' );

?>
	<div class="postbox-container" style="width: 100%;">
	<div class="metabox-holder">	
	<div class="meta-box-sortables">

		<?php
		if ( $view == 'searches' || $view == 'all' )
	        kstats_postbox_charts_search_terms( $view, $limit );
		if ( $view == 'referrers' || $view == 'all' )
			kstats_postbox_charts_referrers( $view, $limit );
		if ( $view == 'agents' || $view == 'all' )
			kstats_postbox_charts_user_agents( $view, $limit );
		if ( $view == 'spiders' || $view == 'all' )
			kstats_postbox_charts_spiders( $view, $limit );
		?>

	</div></div>
	</div> <!-- .postbox-container -->
<?php
}


function kstats_postbox_charts_search_terms( $view = 'all', $limit = '' ) {
	global $wpdb, $kstats;

	$query[] = sprintf( "SELECT SUM( v.count ) AS count, v.search_terms, v.search_engine, v.url, v.referrer
						 FROM ( SELECT count( search_terms ) AS count, search_terms, search_engine, url, referrer
								FROM `%s`
								WHERE search_terms <> '' AND preserved IS NULL
								GROUP BY search_terms
								UNION ALL
								SELECT count, search_terms, search_engine, url, referrer
								FROM `%s`
								WHERE chart = %d )
						 AS v
						 GROUP BY v.search_terms
						 ORDER BY count DESC
						 %s", KSTATS_TABLE_RAW, KSTATS_TABLE_CHARTS, KSTATS_CHARTS_SEARCHES, $limit );
				   
	$results = $wpdb->get_results ( end( $query ) );

	if ( empty( $limit ) )
		$total_results = count( $results );


	$output = '<ul class="options">';

	if ( $view == 'all' ) {
		$output .= '<li><a href="?page=' . $_GET['page'] . '&view=searches">' . __( 'View All Search Terms', 'kstats-reloaded' ) . '</a></li>';
	}
	else {
		$output .= '<li><a href="?page=' . $_GET['page'] . '">' . __( 'Back to Charts', 'kstats-reloaded' ) . '</a></li>';
		$output .= '<li>' . sprintf( __( 'Displaying %d search terms', 'kstats-reloaded' ), $total_results ) . '</li>';
	}

	$output .= '</ul>';

	$output .= '<table class="kstatsfat">';
	$output .= '<thead><tr>';
	$output .= '<th scope="col">' . __( 'Terms', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col">' . __( 'Engine', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col" style="text-align: center;">' . __( 'Views', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col" style="width: 16px;"></th>';
	$output .= '</tr></thead>';

	$output .= '<tbody>';

	if ( is_null( $results ) || empty( $results ) ) {
		$output .= '<tr><td colspan="4" style="padding: 4em; text-align: center;"><strong>' . __( 'No search terms to report yet.', 'kstats-reloaded' ) . '</strong></td></tr>';
	}
	else {

		foreach ( $results as $the ) {
			$output .= '<tr>';
			$output .= '<td><a href="' . kstats_entities( $the->referrer ). '">' . $the->search_terms . '</a></td>';
			$output .= '<td>' . $the->search_engine . '</td>';
			$output .= '<td style="text-align: center;">' . $the->count . '</td>';
			$output .= '<td><a href="' . $kstats->blog_url . ( strpos ( $the->url, 'index.php' ) === FALSE ? kstats_entities( $the->url ) : '' ) . '"><img src="' . KSTATS_PLUGIN_MEDIA . 'page_go.png" width="16" height="16" title="' . __( 'Page viewed', 'kstats-reloaded' ) . '" /></a></td>';
			$output .= '</tr>';
		}

	}

	$output .= '</tbody></table>';

	kstats_build_postbox( 'chart_search_terms', __( 'Top Search Terms', 'kstats-reloaded' ), $output );

}


function kstats_postbox_charts_referrers( $view = 'all', $limit = '' ) {
	global $wpdb, $kstats;

	$query[] = sprintf( "SELECT SUM( v.count ) AS count, v.referrer, v.url
						 FROM ( SELECT count( referrer ) AS count, referrer, url 
								FROM `%s`
								WHERE referrer NOT LIKE '%s%%' AND referrer <> '' AND search_engine = '' AND url <> '' AND preserved IS NULL
								GROUP BY referrer
								UNION ALL
								SELECT count, referrer, url
								FROM `%s`
								WHERE chart = %d )
						 AS v
						 GROUP BY v.referrer
						 ORDER BY count DESC
						 %s", KSTATS_TABLE_RAW, $kstats->blog_url, KSTATS_TABLE_CHARTS, KSTATS_CHARTS_REFERRERS, $limit );

	$results = $wpdb->get_results ( end( $query ) );

	if ( empty( $limit ) )
		$total_results = count( $results );


	$output = '<ul class="options">';

	if ( $view == 'all' ) {
		$output .= '<li><a href="?page=' . $_GET['page'] . '&view=referrers">' . __( 'View All Referrers', 'kstats-reloaded' ) . '</a></li>';
	}
	else {
		$output .= '<li><a href="?page=' . $_GET['page'] . '">' . __( 'Back to Charts', 'kstats-reloaded' ) . '</a></li>';
		$output .= '<li>' . sprintf( __( 'Displaying %d referrers', 'kstats-reloaded' ), $total_results ) . '</li>';
	}

	$output .= '</ul>';


	$output .= '<table class="kstatsfat">';
	$output .= '<thead><tr>';
	$output .= '<th scope="col">' . __( 'URL', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col" style="text-align: center;">' . __( 'Views', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col" style="width: 16px;"></th>';
	$output .= '</tr></thead>';

	$output .= '<tbody>';

	if ( is_null( $results ) || empty( $results ) ) {
		$output .= '<tr><td colspan="4" style="padding: 4em; text-align: center;"><strong>' . __( 'No referrers to report yet.', 'kstats-reloaded' ) . '</strong></td></tr>';
	}
	else {

		foreach ( $results as $the ) {
			$output .= '<tr>';
			$output .= '<td><a href="' . kstats_entities( $the->referrer ). '">' . kstats_truncate ( $the->referrer, 98 ) . '</a></td>';
			$output .= '<td style="text-align: center;">' . $the->count . '</td>';
			$output .= '<td><a href="' . $kstats->blog_url . ( strpos ( $the->url, 'index.php' ) === FALSE ? kstats_entities( $the->url ) : '' ) . '"><img src="' . KSTATS_PLUGIN_MEDIA . 'page_go.png" width="16" height="16" title="' . __( 'Page viewed', 'kstats-reloaded' ) . '" /></a></td>';
			$output .= '</tr>';
		}

	}

	$output .= '</tbody></table>';

	kstats_build_postbox( 'chart_referrers', __( 'Top Referrers', 'kstats-reloaded' ), $output );

}


function kstats_postbox_charts_user_agents( $view = 'all', $limit = '' ) {
	global $wpdb;

	$query[] = sprintf( "SELECT SUM( v.count ) AS count, v.user_agent, v.os, v.browser
						 FROM ( SELECT count( user_agent ) AS count, user_agent, os, browser
								FROM `%s`
								WHERE user_agent <> '' AND CONCAT( os, browser ) <> '' AND preserved IS NULL
								GROUP BY user_agent
								UNION ALL
								SELECT count, user_agent, os, browser
								FROM `%s`
								WHERE chart = %d )
						 AS v
						 GROUP BY v.user_agent
						 ORDER BY count DESC
						 %s", KSTATS_TABLE_RAW, KSTATS_TABLE_CHARTS, KSTATS_CHARTS_AGENTS, $limit );

	$results = $wpdb->get_results ( end( $query ) );

	if ( empty( $limit ) )
		$total_results = count( $results );


	$output = '<ul class="options">';

	if ( $view == 'all' ) {
		$output .= '<li><a href="?page=' . $_GET['page'] . '&view=agents">' . __( 'View All User Agents', 'kstats-reloaded' ) . '</a></li>';
	}
	else {
		$output .= '<li><a href="?page=' . $_GET['page'] . '">' . __( 'Back to Charts', 'kstats-reloaded' ) . '</a></li>';
		$output .= '<li>' . sprintf( __( 'Displaying %d user agents', 'kstats-reloaded' ), $total_results ) . '</li>';
	}

	$output .= '</ul>';


	$output .= '<table class="kstatsfat">';
	$output .= '<thead><tr>';
	$output .= '<th scope="col">' . __( 'Operating System', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col">' . __( 'Browser', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col" style="text-align: center;">' . __( 'Visits', 'kstats-reloaded' ) . '</th>';
	$output .= '</tr></thead>';

	$output .= '<tbody>';

	if ( is_null( $results ) || empty( $results ) ) {
		$output .= '<tr><td colspan="4" style="padding: 4em; text-align: center;"><strong>' . __( 'No user agents to report yet.', 'kstats-reloaded' ) . '</strong></td></tr>';
	}
	else {

		foreach ( $results as $the ) {
			$output .= '<tr>';
			$output .= '<td><abbr title="' . kstats_entities( $the->user_agent ) . '">' . $the->os . '</abbr></td>';
			$output .= '<td><abbr title="' . kstats_entities( $the->user_agent ) . '">' .  $the->browser . '</abbr></td>';
			$output .= '<td style="text-align: center;">' . $the->count . '</td>';
			$output .= '</tr>';
		}

	}

	$output .= '</tbody></table>';

	kstats_build_postbox( 'chart_agents', __( 'Top User Agents', 'kstats-reloaded' ), $output );

}


function kstats_postbox_charts_spiders( $view = 'all', $limit = '' ) {
	global $wpdb;

	$query[] = sprintf( "SELECT SUM( v.count ) AS count, v.user_agent, v.spider, v.url
						 FROM ( SELECT COUNT( user_agent ) AS count, user_agent, spider, url
								FROM `%s`
								WHERE spider <> '' AND url <> '' AND url <> '/robots.txt' AND preserved IS NULL
								GROUP BY user_agent
								UNION ALL
								SELECT count, user_agent, spider, url
								FROM `%s`
								WHERE chart = %d )
						 AS v
						 GROUP BY v.user_agent
						 ORDER BY count DESC
						 %s", KSTATS_TABLE_RAW, KSTATS_TABLE_CHARTS, KSTATS_CHARTS_SPIDERS, $limit );

	$results = $wpdb->get_results ( end( $query ) );

	if ( empty( $limit ) )
		$total_results = count( $results );


	$output = '<ul class="options">';

	if ( $view == 'all' ) {
		$output .= '<li><a href="?page=' . $_GET['page'] . '&view=spiders">' . __( 'View All Spiders', 'kstats-reloaded' ) . '</a></li>';
	}
	else {
		$output .= '<li><a href="?page=' . $_GET['page'] . '">' . __( 'Back to Charts', 'kstats-reloaded' ) . '</a></li>';
		$output .= '<li>' . sprintf( __( 'Displaying %d spiders', 'kstats-reloaded' ), $total_results ) . '</li>';
	}

	$output .= '</ul>';


	$output .= '<table class="kstatsfat">';
	$output .= '<thead><tr>';
	$output .= '<th scope="col">' . __( 'Spider', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col">' . __( 'Last Page Visited', 'kstats-reloaded' ) . '</th>';
	$output .= '<th scope="col" style="text-align: center;">' . __( 'Visits', 'kstats-reloaded' ) . '</th>';
	$output .= '</tr></thead>';

	$output .= '<tbody>';

	if ( is_null( $results ) || empty( $results ) ) {
		$output .= '<tr><td colspan="4" style="padding: 4em; text-align: center;"><strong>' . __( 'No spider visits to report yet.', 'kstats-reloaded' ) . '</strong></td></tr>';
	}
	else {

		foreach ( $results as $the ) {
			$output .= '<tr>';
			$output .= '<td><abbr title="' . kstats_entities( $the->user_agent ) . '">' . $the->spider . '</abbr></td>';
			$output .= '<td style="color: #757575;"><a href="' . kstats_entities( $the->url ). '">' . kstats_truncate ( $the->url, 50 ) . '</a></td>';
			$output .= '<td style="text-align: center;">' . $the->count . '</td>';
			$output .= '</tr>';
		}

	}

	$output .= '</tbody></table>';

	kstats_build_postbox( 'chart_spiders', __( 'Top Spiders', 'kstats-reloaded' ), $output );

}
