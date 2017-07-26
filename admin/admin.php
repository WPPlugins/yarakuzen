<?php

add_action( 'admin_menu', 'yarakuzen_admin_menu' );

function yarakuzen_admin_menu() {
	$hook_suffix = add_options_page(
		__( 'YarakuZen', 'yarakuzen' ),
		__( 'YarakuZen', 'yarakuzen' ),
		'manage_options', 'yarakuzen', 'yarakuzen_admin_settings_page' );

	add_action( 'load-' . $hook_suffix, 'yarakuzen_admin_load_page' );
}

function yarakuzen_admin_load_page() {
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
		check_admin_referer( 'yarakuzen_settings' );

		$public_key = isset( $_POST['publickey'] )
			? trim( $_POST['publickey'] ) : '';
		$private_key = isset( $_POST['privatekey'] )
			? trim( $_POST['privatekey'] ) : '';

		if ( $public_key && $private_key ) {
			YarakuZen::update_option( 'public_key', $public_key );
			YarakuZen::update_option( 'private_key', $private_key );
			$redirect_to = add_query_arg( array( 'message' => 'success' ),
				menu_page_url( 'yarakuzen', false ) );
		} else {
			$redirect_to = add_query_arg( array( 'message' => 'invalid' ),
				menu_page_url( 'yarakuzen', false ) );
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}
}

add_action( 'yarakuzen_admin_notices', 'yarakuzen_admin_notices' );

function yarakuzen_admin_notices() {
	$message = isset( $_REQUEST['message'] ) ? trim( $_REQUEST['message'] ) : '';

	if ( 'invalid' == $message ) {
		echo sprintf(
			'<div class="error notice notice-error is-dismissible"><p><strong>%1$s</strong>: %2$s</p></div>',
			esc_html( __( "ERROR", 'yarakuzen' ) ),
			esc_html( __( "Invalid key values.", 'yarakuzen' ) ) );
	}

	if ( 'success' == $message ) {
		echo sprintf(
			'<div class="updated notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( __( 'Settings saved.', 'yarakuzen' ) ) );
	}
}

function yarakuzen_admin_settings_page() {
	$public_key = yarakuzen_api_public_key();
	$private_key = yarakuzen_api_private_key();
?>
<div class="wrap">

<h1><?php echo esc_html( __( 'YarakuZen Settings', 'yarakuzen' ) ); ?></h1>
<?php do_action( 'yarakuzen_admin_notices' ); ?>

<p><a href="<?php echo esc_url( yarakuzen_app_url( 'wordpress' ) ); ?>" target="_blank" class="button button-secondary"><?php echo esc_html( __( 'Sign up for a YarakuZen account', 'yarakuzen' ) ); ?></a></p>

<h3 class="title"><?php echo esc_html( __( 'API Key', 'yarakuzen' ) ); ?></h3>

<p><a href="<?php echo esc_url( yarakuzen_app_url( 'user/apikey' ) ); ?>" target="_blank"><?php echo esc_html( __( "Get your API key", 'yarakuzen' ) ); ?></a></p>

<form method="post" action="<?php menu_page_url( 'yarakuzen' ); ?>" novalidate="novalidate">
<?php wp_nonce_field( 'yarakuzen_settings' ); ?>
<table class="form-table">
<tbody>
<tr>
<th scope="row"><label for="publickey"><?php echo esc_html( __( 'Public Key', 'yarakuzen' ) ); ?></label></th>
<td><input name="publickey" id="publickey" value="<?php echo esc_attr( $public_key ); ?>" class="regular-text code" type="text"></td>
</tr>

<tr>
<th scope="row"><label for="privatekey"><?php echo esc_html( __( 'Private Key', 'yarakuzen' ) ); ?></label></th>
<td><input name="privatekey" id="privatekey" value="<?php echo esc_attr( $private_key ); ?>" class="regular-text code" type="text"></td>
</tr>

<tr>
<th scope="row"><label for="callbackurl"><?php echo esc_html( __( 'Callback URL', 'yarakuzen' ) ); ?></label></th>
<td><p class="description"><?php echo esc_html( __( "Set the following URL as the Callback URL.", 'yarakuzen' ) ); ?></p>
<input name="callbackurl" id="callbackurl" value="<?php echo esc_url( rest_url( 'yarakuzen/v1/update' ) ); ?>" class="large-text code" type="text" readonly="readonly" onfocus="this.select();">
</td>
</tr>
</tbody>
</table>

<p class="submit"><input name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr( __( 'Save Changes', 'yarakuzen' ) ); ?>" type="submit"></p>

</form>
</div>
<?php
}

add_action( 'admin_enqueue_scripts', 'yarakuzen_admin_enqueue_scripts' );

function yarakuzen_admin_enqueue_scripts( $hook_suffix ) {
	wp_enqueue_style( 'yarakuzen-admin',
		yarakuzen_plugin_url( 'admin/css/style.css' ),
		array(), YARAKUZEN_VERSION, 'all' );

	if ( is_rtl() ) {
		wp_enqueue_style( 'yarakuzen-admin-rtl',
			yarakuzen_plugin_url( 'admin/css/rtl.css' ),
			array(), YARAKUZEN_VERSION, 'all' );
	}

	wp_enqueue_script( 'yarakuzen-admin',
		yarakuzen_plugin_url( 'admin/js/script.js' ),
		array( 'jquery' ), YARAKUZEN_VERSION, true );

	wp_localize_script( 'yarakuzen-admin', '_yarakuzen', array() );
}

add_action( 'add_meta_boxes', 'yarakuzen_admin_meta_boxes', 10, 2 );

function yarakuzen_admin_meta_boxes( $post_type, $post ) {
	if ( ! in_array( $post_type, array( 'post', 'page' ) ) ) {
		return;
	}

	add_meta_box( 'yarakuzen_submitdiv', __( 'YarakuZen', 'yarakuzen' ),
		'yarakuzen_admin_meta_box_callback', null, 'side', 'high' );
}

function yarakuzen_admin_meta_box_callback( $post ) {
	$response = get_post_meta( $post->ID, '_yarakuzen', true );
	$error_code = '';

	if ( is_array( $response ) && isset( $response['errors'][0]['code'] ) ) {
		$error_code = $response['errors'][0]['code'];
	}

	if ( $response && ! $error_code ) {
?>
<p><button type="button" id="yarakuzen-retrieve-translation" class="button button-secondary"><?php echo esc_html( __( 'Retrieve Translation', 'yarakuzen' ) ); ?></button>
<span class="spinner"></span></p>
<?php
		$link = yarakuzen_app_url( 'api-documents' );
		$link = add_query_arg( array(
			'id' => 'post-' . $post->ID,
			'hash' => yarakuzen_api_public_key() ), $link );

		echo sprintf( '<p><a href="%1$s" target="_blank">%2$s</a></p>',
			$link, esc_html( __( 'Edit this translation on YarakuZen', 'yarakuzen' ) ) );

	} else {
		if ( $error_code ) {
			if ( 'apiRequestTextSizeExcessive' == $error_code ) {
				$error = __( "Failed to send to YarakuZen. Post content contains excessively long words.", 'yarakuzen' );
			} elseif ( 'characterLimitReached' == $error_code ) {
				$error = __( "Failed to send to YarakuZen. Post content reaches character length limit.", 'yarakuzen' );
			} else {
				$error = __( "Error occurred.", 'yarakuzen' );
			}
		}

		if ( ! empty( $error ) ) {
			echo sprintf(
				'<div class="notice-error"><p>%s</p></div>', $error );
		}
?>
<p><input type="checkbox" id="yarakuzen-submit" name="yarakuzen-submit" /> <?php echo esc_html( __( 'Send text to YarakuZen when saving.', 'yarakuzen' ) ); ?></p>

<p><?php echo esc_html( __( 'Source Language', 'yarakuzen' ) ); ?><br />
<?php echo yarakuzen_admin_language_select( 'source' ); ?></p>

<p><?php echo esc_html( __( 'Target Language', 'yarakuzen' ) ); ?><br />
<?php echo yarakuzen_admin_language_select( 'target' ); ?></p>
<?php
	}
}

function yarakuzen_admin_language_select( $for = 'target' ) {
	$for_source = ( 'source' == $for );

	if ( $user_opt = get_user_option( 'yarakuzen', get_current_user_id() ) ) {
		$user_opt = $for_source ? $user_opt['src'] : $user_opt['tgt'];
	}

	if ( $for_source ) {
		$options = yarakuzen_source_language_options();
	}

	$output = '';

	foreach ( yarakuzen_available_languages() as $code => $name ) {
		if ( $for_source && ! in_array( $code, $options ) ) {
			continue;
		}

		$selected = ( $code == $user_opt ) ? ' selected="selected"' : '';

		$output .= sprintf( '<option value="%1$s"%2$s>%3$s</option>',
			esc_attr( $code ), $selected, esc_html( $name ) );
	}

	$output = sprintf( '<select name="%1$s" class="yarakuzen-languages-menu">%2$s</select>',
		'yarakuzen-' . ( $for_source ? 'src' : 'tgt' ) . '-lang',
		$output );

	return $output;
}

add_action( 'save_post', 'yarakuzen_admin_save_post', 10, 3 );

function yarakuzen_admin_save_post( $post_id, $post, $update ) {
	// Importing
	if ( did_action( 'import_start' ) && ! did_action( 'import_end' ) ) {
		return;
	}

	// Revision
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( empty( $_POST['yarakuzen-submit'] ) ) {
		return;
	}

	$source_language = isset( $_POST['yarakuzen-src-lang'] )
		? $_POST['yarakuzen-src-lang'] : '';
	$target_language = isset( $_POST['yarakuzen-tgt-lang'] )
		? $_POST['yarakuzen-tgt-lang'] : '';

	if ( ! $source_language || ! $target_language ) {
		return;
	}

	if ( ! class_exists( 'YarakuZenApi_Client' ) ) {
		require_once YARAKUZEN_PLUGIN_DIR . '/includes/yarakuzen-rest-api.php';
	}

	$public_key = yarakuzen_api_public_key();
	$private_key = yarakuzen_api_private_key();

	$client = new YarakuZenApi_Client( $public_key, $private_key );

	$content = $post->post_title . "\n\n" . $post->post_content;
	$text = new YarakuZenApi_TextData();
	$text->customData( 'post-' . $post_id );
	$text->text( $content );

	$request = new YarakuZenApi_RequestPayload();
	$request->lcSrc( $source_language );
	$request->lcTgt( $target_language );
	$request->addText( $text );
	$request->persist();
	$request->machineTranslate();

	$response = $client->postTexts( $request );

	update_post_meta( $post_id, '_yarakuzen', $response );

	update_user_option( get_current_user_id(), 'yarakuzen', array(
		'src' => $source_language,
		'tgt' => $target_language ) );
}

add_action( 'wp_ajax_yarakuzen_retrieve_translation', 'yarakuzen_retrieve_translation_ajax_callback' );

function yarakuzen_retrieve_translation_ajax_callback() {
	$post_id = intval( $_POST['post_id'] );

	if ( ! class_exists( 'YarakuZenApi_Client' ) ) {
		require_once YARAKUZEN_PLUGIN_DIR . '/includes/yarakuzen-rest-api.php';
	}

	$public_key = yarakuzen_api_public_key();
	$private_key = yarakuzen_api_private_key();

	$client = new YarakuZenApi_Client( $public_key, $private_key );

	$text = $client->getTextsByCustomData( 'post-' . $post_id );
	$text = $text['result'][0];
	$text = $text['translation'];

	$text = explode( "\n", $text, 2 );
	header( 'Content-type: application/json' );
	echo wp_json_encode( array(
		'title' => trim( $text[0] ),
		'content' => trim( $text[1] ) ) );

	wp_die();
}
