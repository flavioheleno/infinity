<?php

	class COOKIE {
		//holds class instance for singleton
		private static $instance = null;

		//singleton method - avoids the creation of more than one instance
		public static function singleton() {
			//checks if there is an instance of class, if not, create it
			if (!isset(self::$instance))
				self::$instance = new COOKIE;
			return self::$instance;
		}

		//cleans cookie information
		public function clean() {
			foreach ($_COOKIE as $cookie => $value)
				setcookie($cookie, '', time() - 3600);
		}

		//gets cookie value
		public function __get($index) {
			if (isset($_COOKIE[$index]))
				return $_COOKIE[$index];
			else
				return null;
		}

		//sets cookie value
		public function __set($index, $value) {
			if ($value != '')
				setcookie($index, $value);
			else
				setcookie($index, '', time() - 3600);
		}

		//checks if cookie item is set
		public function __isset($index) {
			return isset($_COOKIE[$index]);
		}

		//unset cookie value
		public function __unset($index) {
			setcookie($index, 'deleted', time() - 3600);
		}

		public function extended_set($name, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false) {
			setcookie($name, $value, (time() + intval($expire)), $path, $domain, $secure, $httponly);
		}

	}

?>
