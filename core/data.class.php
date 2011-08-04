<?php

	class DATA {
		//holds class instances for singleton
		private static $instance = null;
		//holds data values
		private $data = array();

		//singleton method - avoids the creation of more than one instance per data control
		public static function singleton() {
			//checks if there is an instance of class, if not, create it
			if (!(self::$instance instanceof DATA))
				self::$instance = new DATA;
			return self::$instance;
		}

		//sets data item value
		public function __set($index, $value) {
			$this->data[$index] = $value;
		}

		//gets data item value
		public function __get($index) {
			if (isset($this->data[$index]))
				return $this->data[$index];
			return false;
		}

		//checks if data item is set
		public function __isset($index) {
			return isset($this->data[$index]);
		}

		//unset data item
		public function __unset($index) {
			unset($this->data[$index]);
		}

	}

?>
