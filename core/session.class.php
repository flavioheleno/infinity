<?php

	class SESSION {
		//holds class instance for singleton
		private static $instance = null;
		//holds the domain name
		private $domain = '';
		//holds the session name
		private $name = '';
		//sets when the session cookie is valid in subdomain
		private $subdomain = false;
		//holds timeout state of session
		private $timeout = false;

		//class constructor
		public function __construct() {
			$config = CONFIGURATION::singleton();
			if ($config->framework['domain'] != '') {
				$this->name = str_replace('.', '', $config->framework['domain']);
				session_name($this->name);
			}
			$this->domain = $config->framework['domain'];
			$this->subdomain = $config->framework['subdomain'];
			if (!$config->framework['localhost'])
				session_set_cookie_params(0, '/', ($this->subdomain ? '.' : '').$config->framework['domain']);
			session_start();
			if (!isset($_SESSION['timeout']))
				$_SESSION['timeout'] = time() + $config->framework['idletime'];
			else {
				if ($_SESSION['timeout'] < time())
					$this->timeout = true;
				else
					$_SESSION['timeout'] = time() + $config->framework['idletime'];
			}
		}

		//class destructor
		public function __destruct() {
			session_write_close();
		}

		//singleton method - avoids the creation of more than one instance
		public static function singleton() {
			//checks if there is an instance of class, if not, create it
			if (!(self::$instance instanceof SESSION))
				self::$instance = new SESSION;
			return self::$instance;
		}

		//regenerates session id
		public function regenerate() {
			session_regenerate_id(true);
		}

		//destroys entire session information
		public function destroy() {
			setcookie(session_name(), '', (time() - 3600), '/', ($this->subdomain ? '.' : '').$this->domain);
			session_unset();
			session_destroy();
			$_SESSION = array();
		}

		//cleans session information
		public function clean() {
			$_SESSION = array();
		}

		//checks session auth information
		public function check_auth($name) {
			if (isset($_SESSION[$name]))
				return !is_null($_SESSION[$name]);
			return false;
		}

		//checks session timeout
		public function check_timeout() {
			return $this->timeout;
		}

		//gets session value
		public function __get($index) {
			if (isset($_SESSION[$index]))
				return $_SESSION[$index];
			else
				return null;
		}

		//sets session value
		public function __set($index, $value) {
			if ($value != '')
				$_SESSION[$index] = $value;
			else
				unset($_SESSION[$index]);
		}

		//checks if session item is set
		public function __isset($index) {
			return isset($_SESSION[$index]);
		}

		//unset session value
		public function __unset($index) {
			unset($_SESSION[$index]);
		}

	}
