<?php

	require_once __DIR__.'/validator.class.php';
	require_once __DIR__.'/sql.class.php';
	require_once __DIR__.'/../cfg/core/db.config.php';

	abstract class MODEL {
		//instance of sql class
		protected $db = null;
		//instance of auxiliar class
		protected $aux = null;
		//validation rules for data used in this model
		public $rules = array();
		//database fields used in this model
		public $fields = array();

		//class constructor
		public function __construct() {
			$cfg = array(
				'host' => db_host,
				'base' => db_base,
				'user' => db_user,
				'pass' => db_pass,
				'pref' => db_pref,
				'impr' => db_impr,
				'debg' => db_debg
			);
			$this->db = new SQL($cfg);
			$this->aux = AUTOLOAD::loadAuxModel();
		}

		public abstract function loadFields(array $env);

		public function validate() {
			$valid = true;
			foreach ($this->fields as $key => $value)
				if (isset($this->rules[$key]))
					$valid &= VALIDATOR::check($value, $this->rules[$key]);
			return $valid;
		}

	}

?>
