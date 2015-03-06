<?php
/**
 * Plugin Name: HM Slack Bot
 * Description: Slack off with the bot.
 */

namespace HM\Slack\Bot;

use WP_CLI;

require_once __DIR__ . '/inc/namespace.php';
require_once __DIR__ . '/inc/issues/namespace.php';

require_once __DIR__ . '/vendor/autoload.php';
spl_autoload_register( __NAMESPACE__ . '\\autoload' );

// Force using our fork of phpws
require_once __DIR__ . '/phpws/src/Devristo/Phpws/Client/WebSocket.php';

add_filter( 'hm.slack.bot.message', __NAMESPACE__ . '\\Issues\\parse_issue_message', 10, 2 );
add_filter( 'hm.slack.bot.message', __NAMESPACE__ . '\\Issues\\parse_issue_link', 10, 2 );

// Webhook is disabled in favor of the real bot :)
#add_filter( 'wp_ajax_nopriv_hm_slack_webhook', __NAMESPACE__ . '\\handle_webhook' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'hm-slackbot', __NAMESPACE__ . '\\CLI' );
}

/**
 * Send convenience action
 *
 * Rather than always hooking into `message.message` and checking for extra
 * pieces, this lets you do the common case.
 */
add_action( 'hm.slack.bot.message.message', function ( $message, $bot ) {
	if ( empty( $message->text ) || ! empty( $message->subtype ) ) {
		return;
	}

	do_action( 'hm.slack.bot.message', $message, $bot );
}, 10, 2 );

/**
 * Join channels on request
 */
add_action( 'hm.slack.bot.message', function ( $message, $bot ) {
	$pattern = '/^' . $bot->get_matchable_name() . ':? join <#(\w+)>/i';
	if ( ! preg_match( $pattern, $message->text, $matches ) ) {
		return;
	}
	if ( ! $bot->is_admin( $message->user ) ) {
		$bot->send( array(
			'type' => 'message',
			'channel' => $message->channel,
			'text' => 'Sorry, admins only!',
		) );
		return;
	}

	$channel = $matches[1];

	$bot->send( array(
		'type' => 'message',
		'channel' => $message->channel,
		'text' => "I'd join $channel, but you haven't taught me how yet.",
	) );
}, 10, 2 );

/**
 * Reload on request
 */
add_action( 'hm.slack.bot.message', function ( $message, $bot ) {
	$pattern = '/^' . $bot->get_matchable_name() . ':? reload\b/i';
	if ( ! preg_match( $pattern, $message->text ) ) {
		return;
	}
	if ( ! $bot->is_admin( $message->user ) ) {
		$bot->send( array(
			'type' => 'message',
			'channel' => $message->channel,
			'text' => 'Sorry, admins only!',
		) );
		return;
	}

	$bot->send( array(
		'type' => 'message',
		'channel' => $message->channel,
		'text' => 'Be back in a jiffy.',
	) );
	$bot->disconnect();
}, 10, 2 );

/**
 * Reply to hello!
 *
 * This is an example of how to use the bot :)
 */
add_action( 'hm.slack.bot.message', function ( $message, $bot ) {
	$phrases = array(
		'hello',
		'hey',
		'hi',
		'what\'?s up',
		'wassup',
		'(yo ?)+',
		'what\'?s the hiphap',
	);

	$matcher = '(?:' . implode( '|', $phrases ) . ')';
	$name = $bot->get_matchable_name();
	$pattern = "/^($matcher $name|$name:? $matcher)[!?]?/i";
	if ( ! preg_match( $pattern, $message->text ) ) {
		return;
	}

	$reply = array(
		'type' => 'message',
		'channel' => $message->channel,
		'text' => 'Hello to you too!',
	);
	$bot->send( $reply );
}, 10, 2 );
