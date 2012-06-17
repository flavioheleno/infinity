<?php
/**
* Cookie manipulation
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

class COOKIE {
	//holds class instance for singleton
	private static $instance = null;

	//singleton method - avoids the creation of more than one instance
	public static function singleton() {
		//checks if there is an instance of class, if not, create it
		if ((is_null(self::$instance)) || (!(self::$instance instanceof COOKIE)))
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
