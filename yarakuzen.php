<?php
/*
Plugin Name: YarakuZen
Plugin URI: https://yarakuzen.com/
Description: The YarakuZen plugin helps you to translate your site using both machine translation and translation by professionals.
Author: Yaraku, Inc.
Author URI: https://yarakuzen.com/
Text Domain: yarakuzen
Domain Path: /languages/
Version: 1.1.1
*/

define( 'YARAKUZEN_VERSION', '1.1.1' );

define( 'YARAKUZEN_REQUIRED_WP_VERSION', '4.4' );

define( 'YARAKUZEN_PLUGIN', __FILE__ );

define( 'YARAKUZEN_PLUGIN_BASENAME',
	plugin_basename( YARAKUZEN_PLUGIN ) );

define( 'YARAKUZEN_PLUGIN_NAME',
	trim( dirname( YARAKUZEN_PLUGIN_BASENAME ), '/' ) );

define( 'YARAKUZEN_PLUGIN_DIR',
	untrailingslashit( dirname( YARAKUZEN_PLUGIN ) ) );

require_once YARAKUZEN_PLUGIN_DIR . '/includes/functions.php';
require_once YARAKUZEN_PLUGIN_DIR . '/includes/rest-api.php';

if ( is_admin() ) {
	require_once YARAKUZEN_PLUGIN_DIR . '/admin/admin.php';
}

class YarakuZen {
	public static function get_option( $name, $default = false ) {
		$option = get_option( 'yarakuzen' );

		if ( false === $option ) {
			return $default;
		}

		if ( isset( $option[$name] ) ) {
			return $option[$name];
		} else {
			return $default;
		}
	}

	public static function update_option( $name, $value ) {
		$option = get_option( 'yarakuzen' );
		$option = ( false === $option ) ? array() : (array) $option;
		$option = array_merge( $option, array( $name => $value ) );
		update_option( 'yarakuzen', $option );
	}
}

add_action( 'plugins_loaded', 'yarakuzen_load_textdomain' );

function yarakuzen_load_textdomain() {
	load_plugin_textdomain( 'yarakuzen',
		false, YARAKUZEN_PLUGIN_NAME . '/languages' );
}
