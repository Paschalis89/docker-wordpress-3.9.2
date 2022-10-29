<?php 
function hgt_seo_plugin_enqueue_script_and_styles() {
	wp_enqueue_style( 'hgt-seo-custom-styles', plugins_url( '/public/css/style.css', __DIR__ ) );
	wp_enqueue_script( 'hgt-seo-custom-script', plugins_url( '/public/js/admin.js', __DIR__ ) );
	wp_localize_script( 'hgt-seo-custom-script', 'my_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'admin_enqueue_scripts' , 'hgt_seo_plugin_enqueue_script_and_styles' );

function hgt_seo_plugin_enqueue_public_scripts() {
	wp_enqueue_script( 'hgt_seo_ajax_script', plugins_url( '/public/js/script.js', __DIR__ ), 'jQuery', '', true );
    wp_localize_script( 'hgt_seo_ajax_script', 'my_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

}
add_action( 'wp_enqueue_scripts' , 'hgt_seo_plugin_enqueue_public_scripts' );

