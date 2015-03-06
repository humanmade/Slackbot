<?php

namespace HM\Slack\Bot;

use Devristo\Phpws\Client\WebSocket;
use React\EventLoop;
use WP_Error;
use Zend\Log;

class Bot {

	protected $client;

	protected $token;

	protected $connection = null;

	protected $next_id = 1;

	/**
	 * Constructor
	 *
	 * @param string $token OAuth token (or bot auth token)
	 */
	public function __construct( $token ) {
		$this->token = $token;
	}

	/**
	 * Run the bot
	 *
	 * Runs an infinite loop (using React) for the bot
	 */
	public function run() {
		// Don't allow recursive bot creation
		if ( isset( $this->connection ) ) {
			return;
		}

		// Begin a new real-time session
		$session = $this->start_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$this->connection = new Connection( $session );

		do_action( 'hm.slack.bot.started_session', $session, $this );

		$loop = EventLoop\Factory::create();
		$this->client = $this->get_client( $session->url, $loop );

		// Attach events
		$this->client->on( 'request',   array( $this, 'on_request'   ) );
		$this->client->on( 'handshake', array( $this, 'on_handshake' ) );
		$this->client->on( 'connect',   array( $this, 'on_connect'   ) );
		$this->client->on( 'message',   array( $this, 'on_message'   ) );

		$this->client->open();

		do_action( 'hm.slack.bot.connected', $this->client, $this );

		// Begin our loop
		$loop->run();
	}

	protected function start_session() {
		$options = array(
			'blocking' => true,
		);
		return $this->send_via_api( 'rtm.start', array(), $options );
	}

	protected function get_client( $url, $loop ) {
		$logger = new Log\Logger();
		$writer = new Log\Writer\Stream( STDOUT );
		$writer = new Log\Writer\Null();
		$logger->addWriter($writer);

		return new WebSocket( $url, $loop, $logger );
	}

	public function on_request( $headers ) {
		// ...
		// var_dump('request');
	}

	public function on_handshake() {
		// var_dump('handshake');
		// ...
	}

	public function on_connect() {
		// var_dump('connect');
	}

	/**
	 * Callback for message receive
	 *
	 * @param Devristo\Phpws\Messaging\MessageInterface $message Received message
	 */
	public function on_message( $message ) {
		$content = $message->getData();
		$data = json_decode( $content );
		if ( $data === null || empty( $data->type ) ) {
			return;
		}

		do_action( sprintf( 'hm.slack.bot.message.%s', $data->type ), $data, $this );
	}

	/**
	 * Get the current connection instance
	 *
	 * @return Connection|null
	 */
	public function get_connection() {
		return $this->connection;
	}

	/**
	 * Send a message to Slack
	 *
	 * @param array $data Data to send to Slack
	 * @return int Message ID, to allow monitoring replies
	 */
	public function send( $data ) {
		$id = $this->next_id;
		$this->next_id++;

		$data = (array) $data;
		$data['id'] = $id;

		var_dump( $data );

		$encoded = json_encode( $data );
		$this->client->send( $encoded );

		return $id;
	}

	public function send_via_api( $method, $data = array(), $options = array() ) {
		$url = sprintf( 'https://slack.com/api/%s', $method );
		$defaults = array(
			'token' => $this->token,
		);
		$data = array_merge( $defaults, $data );

		$default_options = array(
			'body' => $data,
			'blocking' => false,
		);
		$options = array_merge( $default_options, $options );
		$response = wp_remote_post( $url, $options );
		if ( is_wp_error( $response ) || ! $options['blocking'] ) {
			return $response;
		}

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return new WP_Error( 'hm.slack.bot.bad_response', '', compact( 'url', 'options', 'response' ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );
		if ( empty( $data ) || $data->ok !== true ) {
			return new WP_Error( 'hm.slack.bot.bad_response', '', compact( 'url', 'options', 'response' ) );
		}

		return $data;
	}

	/**
	 * Disconnect from the server
	 */
	public function disconnect() {
		do_action( 'hm.slack.bot.disconnect', $this );

		$this->client->close();
	}

	/**
	 * Get the bot's name
	 *
	 * Hardcoded for now...
	 *
	 * @return string
	 */
	public function get_name() {
		return 'rmbot';
	}

	public function get_matchable_name() {
		return '(?:<@U03M7H4V5>|rmbot)';
	}

	public function is_admin( $user ) {
		// Only rmccue
		return $user === 'U03BWLTDD';
	}
}
