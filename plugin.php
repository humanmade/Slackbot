<?php
/**
 * Plugin Name: HM Slack Bot
 * Description: Slack off with the bot.
 */

namespace HM\Slack\Bot;

require_once __DIR__ . '/inc/namespace.php';
require_once __DIR__ . '/inc/issues/namespace.php';

spl_autoload_register( __NAMESPACE__ . '\\autoload' );

add_filter( 'hm.slack.bot.handle_message', __NAMESPACE__ . '\\Issues\\parse_issue_message', 10, 2 );
add_filter( 'wp_ajax_nopriv_hm_slack_webhook', __NAMESPACE__ . '\\handle_webhook' );
