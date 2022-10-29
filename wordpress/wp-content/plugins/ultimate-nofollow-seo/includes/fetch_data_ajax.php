<?php
function hgt_seo_uns_get_db_results() {
	$get_form_seo_data = get_option( 'hgt_seo_form_data');
	print_r ( json_encode($get_form_seo_data ) );
	die();
}
add_action( 'wp_ajax_nopriv_uns_get_db_results', 'hgt_seo_uns_get_db_results' );
add_action( 'wp_ajax_uns_get_db_results', 'hgt_seo_uns_get_db_results' );

function hgt_seo_uns_get_results_by_id ()  {
	$id = $_REQUEST['value'];
	$get_form_seo_data = get_option( 'hgt_seo_form_data');
	print_r ( $get_form_seo_data[$id]);
	die();
}
add_action( 'wp_ajax_nopriv_uns_get_element_by_id', 'hgt_seo_uns_get_results_by_id' );
add_action( 'wp_ajax_uns_get_element_by_id', 'hgt_seo_uns_get_results_by_id' );

function hgt_seo_uns_update_form_values() {
	parse_str($_REQUEST['values'], $output );
	( isset( $output['uns_selector' ] ) ) ? $selector = sanitize_text_field ( $output['uns_selector' ] ) : $selector = null;
	( isset( $output['uns_element' ] ) ) ? $element = sanitize_text_field ( $output['uns_element' ] ) : $element = null;
	( isset( $output['uns_baseurl' ] ) ) ? $base_url = esc_url ( $output['uns_baseurl' ] ) : $base_url = null;
	( isset( $output['uns_reverse' ] ) ) ? $reverse = $output['uns_reverse' ] : $reverse = 'off';
	( isset( $output['uns_id' ] ) ) ? $id = sanitize_text_field($output['uns_id' ] ) : $id = null;
	$values = $selector.','.$element.','.$base_url.','.$reverse;
	$get_form_seo_data = get_option( 'hgt_seo_form_data');
	$get_form_seo_data[$id] = $values;
	update_option( 'hgt_seo_form_data', $get_form_seo_data );
	echo 'success';
	die();
}
add_action( 'wp_ajax_nopriv_uns_update_form_values', 'hgt_seo_uns_update_form_values' );
add_action( 'wp_ajax_uns_update_form_values', 'hgt_seo_uns_update_form_values' );

function hgt_seo_uns_delete_element_by_id() {
	$id = $_REQUEST['id'];
	$data = get_option( 'hgt_seo_form_data');
	$count = count ( $data );
	if ( $data !=false && $count == 1 ) {
		delete_option( 'hgt_seo_form_data' );
		echo 'success';
	}
	else if ( $data != false ) {
		array_splice($data, $id, 1);
		update_option( 'hgt_seo_form_data', $data );
		echo 'success';
	}
	die();
}
add_action( 'wp_ajax_nopriv_delete_element_by_id', 'hgt_seo_uns_delete_element_by_id' );
add_action( 'wp_ajax_delete_element_by_id', 'hgt_seo_uns_delete_element_by_id' );