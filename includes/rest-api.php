<?php

add_action( 'rest_api_init', 'yarakuzen_rest_api_init' );

function yarakuzen_rest_api_init() {
	register_rest_route( 'yarakuzen/v1', '/update', array(
		'methods' => 'POST',
    'callback' => 'yarakuzen_rest_callback_update' ) );
}

function yarakuzen_rest_callback_update( $request ) {
	$custom_data = $request->get_param( 'customData' );

	if ( empty( $custom_data ) ) {
		return;
	}

	if ( preg_match( '/^post-([0-9]+)$/', $custom_data, $matches ) ) {
		$post_id = absint( $matches[1] );
	} else {
		return;
	}

	$meta = get_post_meta( $post_id, '_yarakuzen', true );

	if ( empty( $meta ) || ! empty( $meta['errors'] ) ) {
		return;
	}

	if ( ! class_exists( 'YarakuZenApi_Client' ) ) {
		require_once YARAKUZEN_PLUGIN_DIR . '/includes/yarakuzen-rest-api.php';
	}

	$public_key = yarakuzen_api_public_key();
	$private_key = yarakuzen_api_private_key();

	$client = new YarakuZenApi_Client( $public_key, $private_key );

	$text = $client->getTextsByCustomData( 'post-' . $post_id );

	if ( ! isset( $text['result'] ) ) {
		return;
	}

	$text = $text['result'][0];
	$text = $text['translation'];
	$text = explode( "\n", $text, 2 );

	$post_id = wp_update_post( array(
		'ID' => $post_id,
		'post_title' => trim( $text[0] ),
		'post_content' => trim( $text[1] ) ) );
}
