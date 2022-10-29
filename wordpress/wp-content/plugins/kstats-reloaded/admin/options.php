<?php

function kstats_options_update_cron( $new_schedule = 'daily' ) {
	global $kstats;

	if ( in_array( $new_schedule, array( 'daily', 'twicedaily', 'hourly' ) ) ) {
		$old_schedule = wp_get_schedule( 'kstats_aggregate_hook' );

		if ( $new_schedule != $old_schedule ) {
			wp_unschedule_event( wp_next_scheduled( 'kstats_aggregate_hook' ), 'kstats_aggregate_hook' );

			switch ( $new_schedule ) {
				case 'daily':
					$cron = strtotime( date( 'Y/m/d', strtotime( '+1 day', $kstats->datetime->timestamp ) ) . ' 12:01am' );
					break;

				case 'twicedaily':
					$cron = strtotime( date( 'Y/m/d', strtotime( '+12 hours', $kstats->datetime->timestamp ) ) . ' 12:01am' );
					break;

				case 'hourly':
					$cron = strtotime( date( 'Y/m/d g:ia', strtotime( '+1 hour', $kstats->datetime->timestamp ) ) );
					break;

			}

			wp_schedule_event( $cron, $new_schedule, 'kstats_aggregate_hook' );

		}

	}

}



function kstats_display_options() {
	global $kstats;

	$kstats->refresh_options();

	if ( isset( $_POST['kstats_scheduler'] ) )
		kstats_options_update_cron( $_POST['kstats_scheduler'] );

?>
	<div class="postbox-container" style="width: 70%;">
	<div class="metabox-holder">	
	<div class="meta-box-sortables">
    	<form action="" id="kstatsadmin" method="post">
        <?php
			kstats_postbox_main_display_scheduler();
		?>
        </form>

		<form action="options.php" id="kstatsadmin" method="post">
		<?php
        	settings_fields('kstats_admin_options');

			kstats_postbox_main_display_options();
			kstats_postbox_main_database_options();
			kstats_postbox_main_access_control();
			kstats_postbox_main_ignore_lists()
		?>
		</form>
	</div></div>
	</div> <!-- .postbox-container -->

	<div class="postbox-container" style="width: 24%;">
	<div class="metabox-holder">	
	<div class="meta-box-sortables">
	<?php
		kstats_postbox_support();
		_kstats_load_library( KSTATS_UTILITIES_BASE );
	?>
	</div></div>
	</div> <!-- .postbox-container -->

<?php

}


function kstats_postbox_main_display_scheduler() {
	global $kstats;


	$schedule = wp_get_schedule( 'kstats_aggregate_hook' );

	$options = array();

	$options[] = array( 'id'    => 'kstats_scheduler',
					    'name'  => __( 'Aggregate Schedule:', 'kstats-reloaded' ),
						'desc'  => __( 'Determines the interval between runs. For higher traffic sites this should be run more often.', 'kstats-reloaded' ),
						'input' => '<input name="kstats_scheduler" type="radio" value="daily" ' . checked( 'daily', $schedule, FALSE ) . ' /><span>' . __( 'Daily', 'kstats-reloaded' ) . '</span>' .
								   '<input name="kstats_scheduler" type="radio" value="twicedaily" ' . checked( 'twicedaily', $schedule, FALSE ) . ' /><span>' . __( 'Twice Daily', 'kstats-reloaded' ) . '</span>' .
								   '<input name="kstats_scheduler" type="radio" value="hourly" ' . checked( 'hourly', $schedule, FALSE ) . ' /><span>' . __( 'Hourly', 'kstats-reloaded' ) . '</span>'
					   );


	$output  = '<div class="intro">';
	$output .= '<p>' . __( 'kStats is activated by default to run an aggregate once daily just after midnight. If your site receives a large amount of traffic, you may want to change this setting to run more often for performance reasons.', 'kstats-reloaded' ) . '</p>';
	$output .= '<p>' . __( 'Next scheduled run is at:', 'kstats-reloaded' ) . ' <strong>' .  date( 'g:ia \o\n D, F jS', wp_next_scheduled( 'kstats_aggregate_hook' ) ) . '</strong></p>';
	$output .= '</div>';

	$output .= kstats_options_build_form( $options, 'primary' );

	kstats_build_postbox( 'display_options', __( 'Aggregate Schedule', 'kstats-reloaded' ), $output );

}



function kstats_postbox_main_display_options() {
	global $kstats;

	$output  = "<script type=\"text/javascript\">\n";

	if ( $kstats->config['dashboard']['display_widget'] == 0 ) {

		$output .= "\tjQuery(document).ready(function($) {\n";
		$output .= "\t\t$('.hide').hide()\n";
		$output .= "\t})\n";
	}

	$output .= "\tfunction toggleDW() {\n";
	$output .= "\t\tjQuery('.hide').slideToggle();\n";
	$output .= "\t}\n";
	$output .= "\tfunction hideDW() {\n";
	$output .= "\t\tjQuery('.hide').hide();\n";
	$output .= "\t}\n";
	$output .= "</script>\n";

	$output .= '<div class="intro">';
	$output .= '<p>' . __( 'The following options control how kStats will function and display your statistics information.', 'kstats-reloaded' ) . '</p>';
	$output .= '</div>';

	$options = array();

	$options[] = array( 'id'    => 'dashboard_display_widget',
					    'name'  => __( 'Dashboard Widget:', 'kstats-reloaded' ),
						'desc'  => __( 'Display a widget on your dashboard with an overview chart and bar graph.', 'kstats-reloaded' ),
						'input' => '<input name="kstats_options[dashboard_display_widget]" type="radio" onClick="toggleDW()" value="1" ' . checked( '1', $kstats->config['dashboard']['display_widget'], FALSE ) . ' /><span>' . __( 'Yes', 'kstats-reloaded' ) . '</span>' .
								   '<input name="kstats_options[dashboard_display_widget]" type="radio" onClick="toggleDW()" value="0" ' . checked( '0', $kstats->config['dashboard']['display_widget'], FALSE ) . ' /><span>' . __( 'No', 'kstats-reloaded' ) . '</span>'
					   );

	$options[] = array( 'id'    => 'dashboard_days_to_show',
					    'class' => 'hide',
					    'name'  => __( 'Dashboard Days to Display:', 'kstats-reloaded' ),
						'desc'  => __( 'How many days of data to display on the dashboard widget graph.', 'kstats-reloaded' ),
						'input' => '<input name="kstats_options[dashboard_days_to_show]" type="text" value="' . $kstats->config['dashboard']['days_to_show'] . '" />'
					   );

	$options[] = array( 'id'    => 'overview_days_to_show',
					    'name'  => __( 'Overview Days to Display:', 'kstats-reloaded' ),
						'desc'  => __( 'How many days of data to display on the primary bar graph.', 'kstats-reloaded' ),
						'input' => '<input name="kstats_options[overview_days_to_show]" type="text" value="' . $kstats->config['overview']['days_to_show'] . '" />'
					   );

	$options[] = array( 'id'    => 'overview_recent_hits_to_show',
					    'name'  => __( 'Recent Hits to Display:', 'kstats-reloaded' ),
						'desc'  => __( 'How many recent hits you would displayed on the statistics overview page.', 'kstats-reloaded' ),
						'input' => '<input name="kstats_options[overview_recent_hits_to_show]" type="text" value="' . $kstats->config['overview']['recent_hits_to_show'] . '" />'
					   );

	$output .= kstats_options_build_form( $options );

	kstats_build_postbox( 'display_options', __( 'Display Options', 'kstats-reloaded' ), $output );

}



function kstats_postbox_main_database_options() {
	global $kstats;

	$options = array();

	$options[] = array( 'id'    => 'database_day_to_keep',
					    'name'  => __( 'Days to Keep:', 'kstats-reloaded' ),
						'desc'  => __( 'How many days of aggregate data to retain (0=unlimited).', 'kstats-reloaded' ),
						'input' => '<input name="kstats_options[database_days_to_keep]" type="text" value="' . $kstats->config['database']['days_to_keep'] . '" />'
					   );

	$options[] = array( 'id'    => 'database_months_to_keep',
					    'name'  => __( 'Months to Keep:', 'kstats-reloaded' ),
						'desc'  => __( 'How many months of aggregate data to retain (0=unlimited).', 'kstats-reloaded' ),
						'input' => '<input name="kstats_options[database_months_to_keep]" type="text" value="' . $kstats->config['database']['months_to_keep'] . '" />'
					   );

	$options[] = array( 'id'    => 'database_rows_to_keep',
					    'name'  => __( 'Raw Data to Keep:', 'kstats-reloaded' ),
						'desc'  => __( 'How many rows of raw data you would like to remain after the aggregate runs (0=unlimited).', 'kstats-reloaded' ),
						'input' => '<input name="kstats_options[database_rows_to_keep]" type="text" value="' . $kstats->config['database']['rows_to_keep'] . '" />'
					   );

	$output = '<div class="intro">';
	$output .= '<p>' . __( 'Use the following configuration settings to tell kStats how much information you would like to store in the aggregate tables and retain at any given time in the raw table.', 'kstats-reloaded' ) . '</p>';
	$output .= '</div>';

	$output .= kstats_options_build_form( $options );

	kstats_build_postbox( 'access_control', __( 'Database Options', 'kstats-reloaded' ), $output );
}




function kstats_postbox_main_access_control() {
	global $kstats, $current_user;

	$options = array();

	$options[] = array( 'id'    => 'access_minimum_to_view',
					    'name'  => __( 'View Statistics:', 'kstats-reloaded' ),
						'desc'  => __( 'The minimum access level required to view statistics for your blog.', 'kstats-reloaded' ),
						'input' => '<select name="kstats_options[access_minimum_to_view]">' . kstats_options_dropdown_roles( $kstats->config['access']['minimum_to_view'] ) . '</select>'
					   );

	$options[] = array( 'id'    => 'access_minimum_to_edit',
					    'name'  => __( 'Edit Options:', 'kstats-reloaded' ),
						'desc'  => __( 'The minimum access level required to modify kStats options.', 'kstats-reloaded' ),
						'input' => '<select name="kstats_options[access_minimum_to_edit]">' . kstats_options_dropdown_roles( $kstats->config['access']['minimum_to_edit'] ) . '</select>'
					   );

	$output = '<div class="intro">';
	$output .= '<p>' . __( 'The following options can be used to control how kStats will permit access to various areas of operation.', 'kstats-reloaded' ) . '</p>';

	if ( current_user_can( 'create_users' ) )
		$output .= '<p style="font-style: italic">' . __( 'As the blog owner, you are automatically granted full access; from here you can set the permissions level necessary for other users of the blog to view your statistics or edit the configuration.', 'kstats-reloaded' ) . '</p>';

	$output .= '</div>';

	$output .= kstats_options_build_form( $options );

	kstats_build_postbox( 'access_control', __( 'Access Control', 'kstats-reloaded' ), $output );
}


function kstats_postbox_main_ignore_lists() {
	global $kstats;

	$options = array();

	$options[] = array( 'id'    => 'ignore_ip_list',
					    'name'  => __( 'Ignore List:', 'kstats-reloaded' ),
						'desc'  => __( 'You can use an incomplete address to block entire ranges. Each IP should be on its own line.', 'kstats-reloaded' ),
						'input' => '<textarea name="kstats_options[ignore_ip_list]" type="text" rows="4" cols="20">' . ( is_array( $kstats->config['ignore']['ip_list'] ) ? implode( "\r\n", $kstats->config['ignore']['ip_list'] ) : '' ) . '</textarea>'
					   );

	$output = '<div class="intro">';
	$output .= '<p>' . __( 'Set up a list of IP addresses (including fragments) that you would like kStats to exclude from recording.', 'kstats-reloaded' ) . '</p>';
	$output .= '</div>';

	$output .= kstats_options_build_form( $options );

	kstats_build_postbox( 'ignore_lists', __( 'Ignore Lists', 'kstats-reloaded' ), $output );
}


function kstats_options_build_form( $options, $button = 'secondary' ) {

	$output = '<fieldset>';

	foreach ( $options as $option ) {

		$output .= '<dl' . ( isset( $option['class'] ) ? ' class="' . $option['class'] . '"' : '' ) . '>';
		$output .= '<dt><label for="kstats_options[' . $option['id'] . '">' . $option['name'] . '</label>';

		if ( isset( $option['desc'] ) )
			$output .= '<p>' . $option['desc'] . '</p>';

		$output .= '</dt>';
		$output .= '<dd>' . $option['input'] . '</dd>';
		$output .= '</dl>';

	}

	$output .= '<div style="clear: both;"></div>';
	$output .= '<p class="kstats_submit"><input type="submit" class="button-' . $button . '" value="' . __( 'Save Changes', 'kstats-reloaded' ) . '" /></p>';
	$output .= '</fieldset>';

	return $output;

}


function kstats_postbox_support() {

	$output  = '<p>' . __( 'If you require support, or would like to contribute to the further development of this plugin, please choose one of the following;', 'kstats-reloaded' ) . '</p>';
	$output .= '<ul class="links">';
	$output .= '<li><a href="http://mark.watero.us/">' . __( 'Author Homepage', 'kstats-reloaded' ) . '</a></li>';
	$output .= '<li><a href="http://mark.watero.us/">' . __( 'Plugin Homepage', 'kstats-reloaded' ) . '</a></li>';
	$output .= '<li><a href="http://wordpress.org/extend/plugins/kstats-reloaded/">' . __( 'Rate This Plugin', 'kstats-reloaded' ) . '</a></li>';
	$output .= '<li><a href="http://mark.watero.us/kstats-reloaded/bug-reports/">' . __( 'Bug Reports', 'kstats-reloaded' ) . '</a></li>';
	$output .= '<li><a href="http://mark.watero.us/2009/10/statistics-analysis-wordpress-feature-requests/">' . __( 'Feature Requests', 'kstats-reloaded' ) . '</a></li>';
	$output .= '<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9847234">' . __( 'Donate To The Cause', 'kstats-reloaded' ) . '</a></li>';
	$output .= '</ul>';

	kstats_build_postbox( 'support', 'kStats Reloaded', $output );
}


function kstats_options_dropdown_roles( $selected = FALSE ) {
	global $wp_roles;

//	die( $selected );
	$p = '';
	$r = '';

	$editable_roles = get_editable_roles();

	foreach( $editable_roles as $role => $details ) {
		$name = translate_user_role($details['name'] );
		if ( strtolower( $selected ) == $role ) // Make default first in list
			$p = "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
		else
			$r .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
	}
	return $p . $r;
}
