<?php
/**
 * Plugin name: iZooto Web Push
 * Plugin URI: https://www.izooto.com
 * Description: Browser push notifications for your site, available in Chrome, Safari and Firefox.
 * Author: iZooto
 * Author URI: https://www.izooto.com
 * Version: 3.7.21
 * License: GPL v2 or later
 *
 * @package izooto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IZOOTO_BASE_URL' ) ) {
	define( 'IZOOTO_BASE_URL', plugin_dir_url( __FILE__ ) );
}

define( 'IZVERSION', '3.7.21' );

define( 'IZ_WP_API', 'https://a.izooto.com/wordpress/v2/integrate' );
define( 'IZ_WP_PUSH_API', 'https://a.izooto.com/wordpress/v2/notification-push' );
define( 'IZ_WP_ERROR_LOG_API', 'https://a.izooto.com/wordpress/v2/wp-log-error' );

/**
 * Create izooto object on install & show message
 */
function izooto_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-init.php';
	$iz_obj = new Init();
	Init::izooto_create_notification_tbl();
	$iz_obj->izooto_install_alert();
}
register_activation_hook( __FILE__, 'izooto_activate' );

/**
 * On izooto's plugin deactivate
 */
function izooto_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-init.php';
	$iz_obj = new Init();
	$iz_obj->izooto_uninstall_alert();
}
register_deactivation_hook( __FILE__, 'izooto_deactivate' );

/**
 * Get cookie data after unslash & sanitization
 *
 * @param string $key cookie name.
 */
function izooto_get_cookie_data( $key ) {
	$output = '';
	if ( isset( $_COOKIE[ $key ] ) ) {
		$output = sanitize_text_field( wp_unslash( $_COOKIE[ $key ] ) );
	}
	return $output;
}


add_action('plugins_loaded', 'izooto_init_plugin');

function izooto_init_plugin() {
	if ( is_admin() ) {
		require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
		}
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-init.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/izootosdk.php';
    $izooto = new Init();
    $settings = $izooto->izooto_get_option('izooto-settings');

    if ( empty($settings) ) {
        $settings = $izooto->izooto_empty_config();
        $izooto->izooto_add_option('izooto-settings', $settings);
        return;
    }

    if ( isset($settings['pid'], $settings['token']) && ! empty($settings['pid']) && ! empty($settings['token']) ) {

        require_once plugin_dir_path(__FILE__) . 'includes/izootometa.php';
    }
}
