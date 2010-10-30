<?php

	class SESSION {
		//holds the domain name
		private $domain = '';
		//holds the session name
		private $name = '';
		//sets when the session cooki is valid in subdomain
		private $subdomain = false;
		//holds timeout state of session
		private $timeout = false;
		//holds the limit idle time
		private $idletime = 0;

		//class constructor
		public function __construct($domain, $subdomain = false, $idletime = 1800) {
			if ($domain != '') {
				$this->name = substr($domain, 0, strpos($domain, '.'));
				session_name($this->name);
			}
			$this->domain = $domain;
			$this->subdomain = $subdomain;
			$this->idletime = $idletime;
			session_set_cookie_params(0, '/', ($subdomain ? '.' : '').$domain);
			session_start();
			if (!isset($_SESSION['timeout']))
				$_SESSION['timeout'] = time() + $this->idletime;
			else {
				if ($_SESSION['timeout'] < time())
					$this->timeout = true;
				else
					$_SESSION['timeout'] = time() + $this->idletime;
			}
		}

		//class destructor
		public function __destruct() {
			session_write_close();
		}

		//regenerates session id
		public function regenerate() {
			session_regenerate_id();
		}

		//destroys entire session information
		public function destroy() {
			setcookie(session_name(), '', (time() - 3600), '/', ($subdomain ? '.' : '').$domain);
			session_unset();
			session_destroy();
			$_SESSION = array();
		}

		//cleans session information
		public function clean() {
			$_SESSION = array();
		}

		//checks session auth information
		public function checkAuth($name) {
			if (isset($_SESSION[$name]))
				return !is_null($_SESSION[$name]);
			else
				return false;
		}

		//checks session timeout
		public function checkTimeout() {
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

?>
