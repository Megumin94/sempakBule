<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Null_Model {

	public function __construct() {
		log_message('info', 'Model Class Initialized');
	}

	public function __get($key) {
		return get_instance()->$key;
	}

}
