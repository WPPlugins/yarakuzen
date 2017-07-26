<?php

function yarakuzen_app_url( $path = '' ) {
	$base = 'https://app.yarakuzen.com';
	return rtrim( $base, '/' ) . '/' . ltrim( $path, '/' );
}

function yarakuzen_api_url( $path = '' ) {
	$base = yarakuzen_api_base_url();
	return rtrim( $base, '/' ) . '/' . ltrim( $path, '/' );
}

function yarakuzen_api_base_url() {
	static $url = '';

	if ( ! empty( $url ) ) {
		return $url;
	}

	if ( defined( 'YARAKUZEN_API_BASE_URL' ) ) {
		$url = YARAKUZEN_API_BASE_URL;
	} else {
		$url = 'https://api.yarakuzen.com';
	}

	return $url;
}

function yarakuzen_api_public_key() {
	static $key = '';

	if ( ! empty( $key ) ) {
		return $key;
	}

	if ( defined( 'YARAKUZEN_API_PUBLIC_KEY' ) ) {
		$key = YARAKUZEN_API_PUBLIC_KEY;
	} else {
		$key = YarakuZen::get_option( 'public_key' );
	}

	return $key;
}

function yarakuzen_api_private_key() {
	static $key = '';

	if ( ! empty( $key ) ) {
		return $key;
	}

	if ( defined( 'YARAKUZEN_API_PRIVATE_KEY' ) ) {
		$key = YARAKUZEN_API_PRIVATE_KEY;
	} else {
		$key = YarakuZen::get_option( 'private_key' );
	}

	return $key;
}

function yarakuzen_available_languages() {
	$languages = array(
		'ja' => __( 'Japanese', 'yarakuzen' ),
		'en' => __( 'English', 'yarakuzen' ),
		'zh' => __( 'Simplified Chinese', 'yarakuzen' ),
		'zh_Hant' => __( 'Traditional Chinese', 'yarakuzen' ),
		'ko' => __( 'Korean', 'yarakuzen' ),
		'fr' => __( 'French', 'yarakuzen' ),
		'de' => __( 'German', 'yarakuzen' ),
		'es' => __( 'Spanish', 'yarakuzen' ),
		'it' => __( 'Italian', 'yarakuzen' ),
		'sv' => __( 'Swedish', 'yarakuzen' ),
		'pt' => __( 'Portuguese', 'yarakuzen' ),
		'id' => __( 'Indonesian', 'yarakuzen' ),
		'vi' => __( 'Vietnamese', 'yarakuzen' ),
		'th' => __( 'Thai', 'yarakuzen' ),
		'ms' => __( 'Malay', 'yarakuzen' ),
		'tl' => __( 'Filipino', 'yarakuzen' ),
		'hi' => __( 'Hindi', 'yarakuzen' ) );

	return $languages;
}

function yarakuzen_get_closest_language( $locale ) {
	if ( 'zh_TW' == $locale || 'zh_HK' == $locale ) {
		return 'zh_Hant';
	}

	$locale = explode( '_', $locale, 2 );
	$lang_code = strtolower( $locale[0] );

	if ( array_key_exists( $lang_code, yarakuzen_available_languages() ) ) {
		return $lang_code;
	}

	return false;
}

function yarakuzen_source_language_options() {
	$locales = array( 'en_US' );
	$locales = array_merge( $locales, get_available_languages() );
	$available_languages = array();

	foreach ( $locales as $locale ) {
		if ( $closest_language = yarakuzen_get_closest_language( $locale ) ) {
			$available_languages[] = $closest_language;
		}
	}

	return $available_languages;
}

function yarakuzen_plugin_url( $path = '' ) {
	$url = plugins_url( $path, YARAKUZEN_PLUGIN );

	if ( is_ssl() && 'http:' == substr( $url, 0, 5 ) ) {
		$url = 'https:' . substr( $url, 5 );
	}

	return $url;
}
