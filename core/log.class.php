<?php
/**
* Log handling
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

	class LOG {
		//holds class instances for singleton
		private static $instance = array();
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
					exit(__CLASS__.': can\'t create path ('.$path.')');

			//creates log history
			if ((file_exists($path.'/'.$filename)) && (is_file($path.'/'.$filename))) {
				$ctime = filectime($path.'/'.$filename);
				if (($ctime !== false) && ((date('d') > date('d', $ctime)) || (date('m') > date('m', $ctime)) || (date('Y') > date('Y', $ctime))))
					@rename($path.'/'.$filename, $path.'/'.date('Ymd', $ctime).$filename);
			}

			//open log file
			$this->handler = fopen($path.'/'.$filename, 'a');
			if ($this->handler === false)
				exit(__CLASS__.': can\'t open log file ('.$filename.')');
			$this->add('Log start');
		}

		//class destructor
		public function __destruct() {
			//if log file was oppened, close it
			if ($this->handler !== false) {
				$this->add('Log end');
				fclose($this->handler);
			}
		}

		//singleton method - avoids the creation of more than one instance per log file
		public static function singleton($filename = 'infinity.log', $path = '') {
			//checks if there is an instance of class, if not, create it
			if ((!isset(self::$instance[$filename])) || (!(self::$instance[$filename] instanceof LOG)))
				self::$instance[$filename] = new LOG($filename, $path);
			return self::$instance[$filename];
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
		public function is_enabled() {
			return $this->enabled;
		}

		//add method - adds text to log file
		public function add($text) {
			//if log is enabled and log file was oppened, prints text to it
			if (($this->enabled) && ($this->handler !== false))
				fwrite($this->handler, date('[d/m/Y - H:i:s] ').$text."\n");
		}

		//clean method - truncates log file
		public function clean() {
			//if log file was oppened, truncate it
			if ($this->handler !== false)
				ftruncate($this->handler, 0);
		}

	}
