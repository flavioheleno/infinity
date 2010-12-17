<?php

	class LOG {
		//holds class instance for singleton
		private static $instance = null;
		//holds log file handler
		private $handler = null;
		//holds log enable/disabled
		private $enabled = true;

		//class constructor
		public function __construct($filename, $path) {
			//checks filename
			if (trim($filename) == '')
				$filename = 'data.log';
			else if (substr($filename, -4) != '.log')
				$filename .= '.log';

			//checks path
			if (trim($path) == '')
				$path = __DIR__.'/../log';
			else if (substr($path, -1) == '/')
				$path = substr($path, 0, (strlen($path) - 1));

			//ensure path exists
			if (!file_exists($path))
				if (!mkdir($path))
					die(__CLASS__.': can\'t create path ('.$path.')');

			//open log file
			$this->handler = fopen($path.'/'.$filename, 'a');
		}

		//class destructor
		public function __destruct() {
			//if log file was oppened, close it
			if ($this->handler)
				fclose($this->handler);
		}

		//singleton method - avoids the creation of more than one instance
		public static function singleton($filename = 'data.log', $path = '') {
			//checks if there is an instance of class, if not, create it
			if (!isset(self::$instance))
				self::$instance = new LOG($filename, $path);
			return self::$instance;
		}

		//enables log
		public function enable() {
			$this->enabled = true;
		}

		//disables log
		public function disable() {
			$this->enabled = false;
		}

		//returns log state
		public function isEnabled() {
			return $this->enabled;
		}

		//add method - adds text to log file
		public function add($text) {
			//if log is enabled and log file was oppened, prints text to it
			if (($this->enabled) && ($this->handler))
				fwrite($this->handler, date('[d/m/Y - H:i:s] ').$text."\n");
		}

		//clean method - truncates log file
		public function clean() {
			//if log file was oppened, truncate it
			if ($this->handler)
				ftruncate($this->handler, 0);
		}

	}

?>
