<?php

namespace HM\Slack\Bot;

class Connection {
	public function __construct( $data ) {
		$this->data = $data;
	}

	public function get_channel( $id ) {
		$channels = $this->data->channels;
		$matching = array_filter( $channels, function ( $channel ) use ( $id ) {
			return $channel->id === $id;
		} );

		if ( empty( $matching ) ) {
			return null;
		}

		return $matching[0];
	}

	public function get_channels() {
		return $this->data->channels;
	}

	public function get_user( $id ) {
		$users = $this->data->users;
		$matching = array_filter( $users, function ( $user ) use ( $id ) {
			return $user->id === $id;
		} );

		if ( empty( $matching ) ) {
			return null;
		}

		return $matching[0];
	}

	public function get_users() {
		return $this->data->users;
	}
}