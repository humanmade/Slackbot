<?php

namespace HM\Slack\Bot;

use WP_CLI;
use WP_CLI_Command;

class CLI extends WP_CLI_Command {
	/**
	 * ## OPTIONS
	 *
	 * [--debug]
	 * : Run with debug logging enabled
	 */
	public function run( $_, $assoc_args ) {
		if ( ! defined( 'HM_SLACK_BOT_TOKEN' ) ) {
			return WP_CLI::error( 'Slack bot token is not defined. Please add it to your wp-config' );
		}

		$bot = new Bot( HM_SLACK_BOT_TOKEN );
		$result = $bot->run();
		if ( is_wp_error( $result ) ) {
			$message = sprintf( '%s: %s', $result->get_error_code(), $result->get_error_message() );
			// if ( isset( $assoc_args['debug'] ) ) {
				$message .= PHP_EOL . var_export( $result->get_error_data(), true );
			// }
			WP_CLI::error( $message );
		}
	}
}
