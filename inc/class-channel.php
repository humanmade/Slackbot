<?php

namespace HM\Slack\Bot;

class Channel {
	public function __construct( $data ) {
		$this->data = $data;
	}

	public function get_id() {
		return $this->data->id;
	}

	public function get_name() {
		return $this->data->name;
	}

	public function is_member() {
		return $this->data->is_member;
	}

	public function get_users() {
		return $this->data->members;
	}
}
