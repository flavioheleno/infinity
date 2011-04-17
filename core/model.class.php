<?php

	require_once __DIR__.'/../cfg/core/db.config.php';

	abstract class MODEL {
		//module name
		protected $name = '';
		//instance of data class
		protected $data = null;
		//instance of query class
		protected $query = null;
		//instance of log class
		protected $log = null;
		//validation rules for data used in this model
		protected $rules = array();
		//database fields used in this model
		protected $fields = array();
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
			$this->query = new QUERY($cfg);
		}

		public function load($id, $fullid = false) {
			if ($fullid)
				$file = __DIR__.'/../cfg/form/'.$id.'.json';
			else
				$file = __DIR__.'/../cfg/form/'.$this->name.'_'.$id.'.json';
			if ((file_exists($file)) && (is_file($file))) {
				$src = file_get_contents($file);
				$json = json_decode($src, true);
				if (!is_null($json))
					foreach ($json['fields'] as $field => $properties) {
						$this->rules[$field] = $properties['rules'];
						$this->fields[$field] = $properties['type'].'_'.$field;
					}
			}
		}

		public function unload() {
			$this->rules = array();
			$this->fields = array();
		}

		public function sanitize() {
			foreach ($this->fields as $key => $value)
				if (isset($this->rules[$key]))
					VALIDATOR::sanitize($_REQUEST[$value], $this->rules[$key]);
		}

		public function validate() {
			$valid = true;
			foreach ($this->fields as $key => $value)
				if (isset($this->rules[$key]))
					$valid &= VALIDATOR::check($_REQUEST[$value], $this->rules[$key]);
			return $valid;
		}

	}

?>
