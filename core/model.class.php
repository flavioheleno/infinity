<?php

	require_once __DIR__.'/../cfg/core/db.config.php';

	abstract class MODEL {
		//module name
		protected $name = '';
		//instance of data class
		protected $data = null;
		//instance of query class
		protected $query = null;
		//instance of secure class;
		protected $secure = null;
		//instance of log class
		protected $log = null;
		//validation rules for data used in this model
		protected $rules = array();
		//field values used in this model
		protected $field = array();
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
			//creates secure object
			if (in_array('secure', $this->uses))
				$this->secure = new SECURE;
		}

		public function load($id, $fullid = false) {
			if ($fullid)
				$file = __DIR__.'/../cfg/form/'.$id.'.json';
			else
				$file = __DIR__.'/../cfg/form/'.strtolower($this->name).'_'.$id.'.json';
			if ((file_exists($file)) && (is_file($file))) {
				$src = file_get_contents($file);
				$json = json_decode($src, true);
				if (!is_null($json))
					foreach ($json['fields'] as $field => $properties) {
						if (isset($properties['rules']))
							$this->rules[$field] = $properties['rules'];
						$this->field[$field] = $_REQUEST[$properties['type'].'_'.$field];
					}
			}
		}

		public function unload() {
			$this->rules = array();
			$this->field = array();
		}

		public function sanitize() {
			foreach ($this->field as $field => &$value)
				if (isset($this->rules[$field]))
					VALIDATOR::sanitize($value, $this->rules[$field]);
		}

		public function validate() {
			$valid = true;
			foreach ($this->field as $field => $value)
				if (isset($this->rules[$field]))
					$valid &= VALIDATOR::check($value, $this->rules[$field]);
			return $valid;
		}

	}

?>
