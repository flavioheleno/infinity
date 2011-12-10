<?php
/**
* Configuration handling
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

	class CONFIGURATION {
		//holds class instance for singleton
		private static $instance = null;
		//holds configuration
		private $config = array();
		//instance of log class
		protected $log = null;

		public function __construct() {
			$this->log = LOG::singleton();
			$this->load_core('framework', '_infinity');
		}

		public function load_core($filename, $name = '_infinity') {
			if (!isset($this->$filename)) {
				$this->log->add('Loading core configuration from "'.$filename.'.config.php"');
				$path = PATH::singleton();
				$file = $path->absolute('cfg', 'core').$filename.'.config.php';
				if ((file_exists($file)) && (is_file($file))) {
					require_once $file;
					$this->$filename = ${$name};
				} else
					$this->log->add('File not found: "'.$filename.'.config.php"');
			}
		}

		public function load_app($filename, $name = '_app') {
			if (!isset($this->$filename)) {
				$this->log->add('Loading app configuration from "'.$filename.'.config.php"');
				$path = PATH::singleton();
				$file = $path->absolute('cfg', 'app').$filename.'.config.php';
				if ((file_exists($file)) && (is_file($file))) {
					require_once $file;
					$this->$filename = ${$name};
				} else
					$this->log->add('File not found: "'.$filename.'.config.php"');
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
			if ((is_null(self::$instance)) || (!(self::$instance instanceof CONFIGURATION)))
				self::$instance = new CONFIGURATION;
			return self::$instance;
		}

	}
