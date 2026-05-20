<?php
/**
 * This file contais method to handle izooto SDK part.
 *
 * @package izooto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the izooto query.
 *
 * @param string $query as param.
 */
function izooto_query( $query ) {
		$query[] = 'izooto';
		return $query;
}

/**
 * Included the izooto sdk in every page
 */
function include_izooto_sdk() {
	include_once plugin_dir_path( __FILE__ ) . 'class-init.php';
	$obj        = new Init();
	$opfunction = $obj->izooto_get_option( 'izooto-settings' );
	$base_url   = get_site_url();
    // verify cdn url
	$pid = $opfunction['pid'];
	if ( empty($pid)  ) {
		return;
	}
	$cdnUrl = $opfunction['cdn'];
	if ( empty( $cdnUrl ) || ! is_string( $cdnUrl ) ) {
		return;
	}
	$parsed = parse_url('https://' . ltrim($cdnUrl, '/'));
	$host   = $parsed['host'] ?? '';

	if ( ! preg_match('/(^|\.)izooto\.com$/', $host) ) {
		return;
	}
	$sdkurl =  'https://' . $opfunction['cdn'];
	wp_enqueue_script('izootoWP', esc_url($sdkurl), array(), IZVERSION, true);
	if (is_ssl()) {
			$swPath = file_exists( ABSPATH . 'service-worker.js' )
		? $base_url . '/service-worker.js'
		: IZOOTO_BASE_URL . 'includes/service-worker.php?sw=' .  sha1( $pid ) ;

		$inline_script = "
		window.is_wp = 1;
		window._izootoModule = window._izootoModule || {};
		window._izootoModule['swPath'] = " . wp_json_encode( $swPath, JSON_UNESCAPED_SLASHES ) . ";
						";
		wp_add_inline_script( 'izootoWP', $inline_script, 'before' );
	}
	wp_add_inline_script(
		'izootoWP',
		'window._izq = window._izq || []; window._izq.push(["init"]);',
		'after'
	);
}

/**
 * Expose the izooto sdk helper file on specific url
 *
 * @param string $query as param.
 */
function izooto_sdk_files( $query ) {
	include_once plugin_dir_path( __FILE__ ) . 'class-init.php';
	$obj    = new Init();
	$izooto = '';
	if ( isset( $query->query_vars['izooto'] ) ) {
		$izooto = $query->query_vars['izooto'];
	}
	$opfunction = $obj->izooto_get_option( 'izooto-settings' );

	$sw = $opfunction['sw'];
	if ( empty( $sw ) || ! is_string( $sw ) ) {
		return;
	}
	// Ensure the URL is absolute and valid
	if ( ! preg_match('#^https?://#i', $sw) ) {
    	$sw = 'https://' . ltrim($sw, '/');
	}
	$parsed = parse_url($sw);
	$host   = $parsed['host'] ?? '';

	if ( ! preg_match('/(^|\.)izooto\.com$/', $host) ) {
		return;
	}
	$template = "var izCacheVer = 1; importScripts(" . wp_json_encode($sw, JSON_UNESCAPED_SLASHES) . ");";
	if ( 'sw' === $izooto ) {
		if ( ! headers_sent() ) {
			header('Content-Type: application/javascript');
			header('Service-Worker-Allowed: /');
		}
    echo $template;
    exit;
	}
}

add_filter( 'query_vars', 'izooto_query', 10, 1 );
add_action( 'parse_request', 'izooto_sdk_files' );
add_action( 'wp_enqueue_scripts', 'include_izooto_sdk' );

