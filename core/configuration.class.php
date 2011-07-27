<?php

	class CONFIGURATION {
		//holds class instance for singleton
		private static $instance = null;
		//holds configuration
		private $config = array();

		public function __construct() {
			$this->load_core('framework', '_infinity');
		}

		public function load_core($filename, $name = '_infinity') {
			if (!isset($this->$filename)) {
				$file = __DIR__.'/../cfg/core/'.$filename.'.config.php';
				if ((file_exists($file)) && (is_file($file))) {
					require_once $file;
					$this->$filename = ${$name};
				}
			}
		}

		public function load_app($filename, $name = '_app') {
			if (!isset($this->$filename)) {
				$file = __DIR__.'/../cfg/app/'.$filename.'.config.php';
				if ((file_exists($file)) && (is_file($file))) {
					require_once $file;
					$this->$filename = ${$name};
				}
			}
		}

		public function __get($index) {
			if (isset($this->$index))
				return $this->$index;
			return null;
		}

		//singleton method - avoids the creation of more than one instance
		public static function singleton() {
			//checks if there is an instance of class, if not, create it
			if (!isset(self::$instance))
				self::$instance = new CONFIGURATION;
			return self::$instance;
		}

	}

?>
