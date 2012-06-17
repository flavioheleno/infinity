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
	private static $instance = null;
	//holds log file handler
	private $handler = false;
	//holds log enable/disabled
	private $enabled = true;

	//class constructor
	public function __construct($enabled) {
		//checks path
		$path = PATH::singleton();
		$folder = $path->absolute('log');

		//ensure path exists
		if (!file_exists($folder)) {
			if ((!@mkdir($folder)) || (!@chmod($folder, 0777)))
				return false;
		}
		//open log file
		$filename = date('Ymd').'.log';
		$this->handler = @fopen($folder.$filename, 'a');
		if ($this->handler === false) {
			$this->disable();
			return false;
		}
		$this->enabled = $enabled;
		$this->add('Log start');
		return true;
	}

	//class destructor
	public function __destruct() {
		//if log file was oppened, close it
		if ($this->handler !== false) {
			$this->add('Log end');
			$this->add('');
			fclose($this->handler);
		}
	}

	//singleton method - avoids the creation of more than one instance per log file
	public static function singleton($enabled = false) {
		//checks if there is an instance of class, if not, create it
		if ((is_null(self::$instance)) || (!(self::$instance instanceof LOG)))
			self::$instance = new LOG($enabled);
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
	public function is_enabled() {
		return $this->enabled;
	}

	//add method - adds text to log file
	public function add($text) {
		//if log is enabled and log file was oppened, prints text to it
		if (($this->enabled) && ($this->handler !== false))
			fwrite($this->handler, ($text != '' ? date('[d/m/Y - H:i:s] ').$text."\n" : "\n"));
	}

	//clean method - truncates log file
	public function clean() {
		//if log file was oppened, truncate it
		if ($this->handler !== false)
			ftruncate($this->handler, 0);
	}

}
