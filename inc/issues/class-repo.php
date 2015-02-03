<?php

namespace HM\Slack\Bot\Issues;

class Repo {
	protected $owner;

	protected $name;

	public function __construct( $owner, $name ) {
		$this->owner = $owner;
		$this->name = $name;
	}

	public function get_owner() {
		return $this->owner;
	}

	public function get_name() {
		return $this->name;
	}

	public function get_url() {
		return sprintf( 'https://github.com/%s/%s', urlencode( $this->owner ), urlencode( $this->name ) );
	}
}
