<?php
/**
* Session manipulation
*
* @version 0.1
* @author Flávio Heleno <flaviohbatista@gmail.com>
* @link http://code.google.com/p/infinity-framework
* @copyright Copyright (c) 2010/2011, Flávio Heleno
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*/

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
			session_name($config->framework['session']['name']);
			$this->domain = $config->framework['main']['domain'];
			$this->subdomain = $config->framework['session']['subdomain'];
			if (!$config->framework['session']['localhost'])
				session_set_cookie_params(0, $config->framework['main']['base_path'], ($this->subdomain ? '.' : '').$config->framework['main']['domain']);
			session_start();
			if ($config->framework['session']['idletime'] > 0) {
				if (!isset($_SESSION['timeout']))
					$_SESSION['timeout'] = time() + $config->framework['session']['idletime'];
				else {
					if ($_SESSION['timeout'] < time())
						$this->timeout = true;
					else
						$_SESSION['timeout'] = time() + $config->framework['session']['idletime'];
				}
			}
		}

		//class destructor
		public function __destruct() {
			session_write_close();
		}

		//singleton method - avoids the creation of more than one instance
		public static function singleton() {
			//checks if there is an instance of class, if not, create it
			if ((is_null(self::$instance)) || (!(self::$instance instanceof SESSION)))
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

		public function gen_csrf() {
			$csrf = sha1(microtime(true).session_id());
			$_SESSION['__csrf'] = $csrf;
			return $csrf;
		}

		public function check_csrf($value) {
			if ((isset($_SESSION['__csrf'])) && ($_SESSION['__csrf'] == $value))
				return true;
			return false;
		}

		//gets session value
		public function __get($index) {
			if (isset($_SESSION[$index]))
				return $_SESSION[$index];
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
