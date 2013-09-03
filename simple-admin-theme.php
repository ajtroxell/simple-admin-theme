<?php

/*
Plugin Name: Simple Admin Theme
Plugin URI: http://ajtroxell.com/squar3d-admin-theme
Description: Gives your dashboard a small makeover to a simpler high-contrast theme.
Author: AJ Troxell
Version: 1.2
Author URI: http://ajtroxell.com
*/

require 'plugin-updates/plugin-update-checker.php';
$simple_admin_theme_updates = new PluginUpdateChecker(
    'http://labs.ajtroxell.com/plugins/simple-admin-theme/info.json',
    __FILE__,
    'simple-admin-theme'
);

function my_admin_theme_style() {
    wp_enqueue_style('my-admin-theme', plugins_url('wp-admin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'my_admin_theme_style');
add_action('login_enqueue_scripts', 'my_admin_theme_style');

function simple_admin_theme_presstrends_plugin() {
    // PressTrends Account API Key
    $api_key = '1uv0ak16ziqw785pmqxn0eykq5pmhic3kvqv';
    $auth    = 'k21gf5z188wtv8crxhljzzrz6emgfr6d3';
    // Start of Metrics
    global $wpdb;
    $data = get_transient( 'presstrends_cache_data' );
    if ( !$data || $data == '' ) {
        $api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update/auth/';
        $url      = $api_base . $auth . '/api/' . $api_key . '/';
        $count_posts    = wp_count_posts();
        $count_pages    = wp_count_posts( 'page' );
        $comments_count = wp_count_comments();
        if ( function_exists( 'wp_get_theme' ) ) {
            $theme_data = wp_get_theme();
            $theme_name = urlencode( $theme_data->Name );
        } else {
            $theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
            $theme_name = $theme_data['Name'];
        }
        $plugin_name = '&';
        foreach ( get_plugins() as $plugin_info ) {
            $plugin_name .= $plugin_info['Name'] . '&';
        }
        // CHANGE __FILE__ PATH IF LOCATED OUTSIDE MAIN PLUGIN FILE
        $plugin_data         = get_plugin_data( __FILE__ );
        $posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );
        $data                = array(
            'url'             => stripslashes( str_replace( array( 'http://', '/', ':' ), '', site_url() ) ),
            'posts'           => $count_posts->publish,
            'pages'           => $count_pages->publish,
            'comments'        => $comments_count->total_comments,
            'approved'        => $comments_count->approved,
            'spam'            => $comments_count->spam,
            'pingbacks'       => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ),
            'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0,
            'theme_version'   => $plugin_data['Version'],
            'theme_name'      => $theme_name,
            'site_name'       => str_replace( ' ', '', get_bloginfo( 'name' ) ),
            'plugins'         => count( get_option( 'active_plugins' ) ),
            'plugin'          => urlencode( $plugin_name ),
            'wpversion'       => get_bloginfo( 'version' ),
        );
        foreach ( $data as $k => $v ) {
            $url .= $k . '/' . $v . '/';
        }
        wp_remote_get( $url );
        set_transient( 'presstrends_cache_data', $data, 60 * 60 * 24 );
        }
    }
// PressTrends WordPress Action
add_action('admin_init', 'simple_admin_theme_presstrends_plugin');

?>