<?php
/*
Plugin Name: Ultimate Nofollow SEO
Plugin URI: https://heygotomarketing.com/plugins/ultimate-nofollow-seo/
Description: The Ultimate NoFollow SEO WordPress plugin allows you to add NoFollow tags to links site-wide based on the base url(http://somesite.com), CSS Class & ID, and HTML Tag. There's finally a simple all-in-one solution that lets you manage all your NoFollow links in one place. Create new noFollow links, edit old ones, and delete the ones you don't need. This works perfectly for links that repeat throughout your site that don't need a rel="follow" tag.
Version: 1.1
Author: HeyGoTo
Author URI: http://heygotomarketing.com/
License: GPLv2 or later
*/
if ( ! defined( 'ABSPATH' ) ) exit;

require_once 'includes/functions.php';
require_once 'includes/index.php';
require_once 'includes/fetch_data_ajax.php';

register_deactivation_hook( __FILE__, 'hgt_seo_deactivation_function' );
function hgt_seo_deactivation_function() {
	delete_option('hgt_seo_field_count');
	delete_option('hgt_seo_form_data');
}

add_action ('admin_menu' , 'hgt_build_plugin_menu');
function hgt_build_plugin_menu () {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	add_menu_page( 'hgt-seo-page', 'NoFollow SEO', 'manage_options', 'hgt-seo', 'hgt_seo_my_plugin_options',     plugin_dir_url( __FILE__ ) . '/icon.png');
}
