<?php

namespace HM\Slack\Bot;

use HM\Slack;

function autoload( $class ) {
	$prefix = 'HM\\Slack\\Bot';
	$prefix_length = strlen( $prefix );
	$path = __DIR__ . '/';

	if ( strpos( $class, $prefix . '\\' ) !== 0 ) {
		return;
	}

	// Strip prefix from the start (ala PSR-4)
	$class = substr( $class, $prefix_length + 1 );
	$class = strtolower( $class );
	$file = '';

	if ( false !== ( $last_ns_pos = strripos( $class, '\\' ) ) ) {
		$namespace = substr( $class, 0, $last_ns_pos );
		$class     = substr( $class, $last_ns_pos + 1 );
		$file      = str_replace( '\\', DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;
	}
	$file .= 'class-' . str_replace( '_', '-', $class ) . '.php';

	$path = $path . $file;

	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

/**
 * Handle HTTP request from Slack
 */
function handle_webhook() {
	$data = wp_unslash( $_POST );

	handle_message( $data );

	exit;
}

/**
 * Handle a message from Slack
 *
 * @param array $data Data from Slack message
 */
function handle_message( $data ) {
	$responses = apply_filters( 'hm.slack.bot.handle_message', array(), $data );

	if ( empty( $responses ) ) {
		return false;
	}

	$message = array(
		'attachments' => $responses,
		'channel'     => sprintf( '#%s', $data['channel_name'] ),
	);

	Slack\message( $message );

	return $message;
}