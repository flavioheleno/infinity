<?php

	require_once __DIR__.'/../cfg/core/db.config.php';

	abstract class MODEL {
		//module name
		protected $name = '';
		//instance of data class
		protected $data = null;
		//instance of sql class
		protected $db = null;
		//instance of log class
		protected $log = null;
		//validation rules for data used in this model
		public $rules = array();
		//database fields used in this model
		public $fields = array();
		//sets the helpers needed by class
		protected $uses = array();

		//class constructor
		public function __construct($name, &$log) {
			$this->name = $name;
			$this->log = $log;
			$this->data = DATA::singleton();
			$cfg = array(
				'hostname' => db_hostname,
				'database' => db_database,
				'username' => db_username,
				'password' => db_password,
				'prefix' => db_prefix,
				'mysqli' => db_mysqli,
				'debug' => db_debug
			);
			$this->db = new SQL($cfg);
		}

		public function sanitize() {
			foreach ($this->fields as $key => $value)
				if (isset($this->rules[$key]))
					VALIDATOR::sanitize($value, $this->rules[$key]);
		}

		public function validate() {
			$valid = true;
			foreach ($this->fields as $key => $value)
				if (isset($this->rules[$key]))
					$valid &= VALIDATOR::check($value, $this->rules[$key]);
			return $valid;
		}

	}

?>
