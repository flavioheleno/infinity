<?php

	AUTOLOAD::require_core_config('db');

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
				$file = __DIR__.'/../cfg/form/'.strtolower($id).'.xml';
			else
				$file = __DIR__.'/../cfg/form/'.strtolower($this->name).'_'.$id.'.xml';
			if ((file_exists($file)) && (is_file($file))) {
				$src = file_get_contents($file);
				$xml = new SimpleXMLElement($src);
				if ($xml === false) {
					$this->log->add('Invalid XML file ('.$file.')');
					return false;
				}
				foreach ($xml->fields->field as $item) {
					if (isset($item->rule)) {
						$this->rules[(string)$item['id']] = array();
						foreach ($item->rule as $rule) {
							if (isset($rule['id'])) {
								if (isset($rule['value']))
									$this->rules[(string)$item['id']][(string)$rule['id']] = (string)$rule['value'];
								else
									$this->rules[(string)$item['id']][] = (string)$rule['id'];
							}
						}
					}
					$key = (string)$item['type'].'_'.(string)$item['id'];
					if (isset($_REQUEST[$key]))
						$this->field[(string)$item['id']] = $_REQUEST[$key];
					else
						$this->field[(string)$item['id']] = false;
				}
				return true;
			}
			$this->log->add('File not found: '.$file);
			return false;
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
