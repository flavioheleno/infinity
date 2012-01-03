<?php
/**
* Memcache abstraction
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

	class MCACHE {
		private static $instance = null;
		private $memcache = null;

		public function __construct() {
			$this->memcache = new Memcache;
			$this->memcache->connect('localhost', 11211);
		}

		public static function singleton() {
			if ((is_null(self::$instance)) || (!(self::$instance instanceof MCACHE)))
				self::$instance = new MCACHE;
			return self::$instance;
		}

		public function flush() {
			$this->memcache->flush();
		}

		public function extended_set($index, $value, $ttl) {
			$this->memcache->set($index, serialize($value), false, $ttl);
		}

		public function __set($index, $value) {
			$this->memcache->set($index, serialize($value));
		}

		public function __get($index) {
			return unserialize($this->memcache->get($index));
		}

		public function __isset($index) {
			if ($this->memcache->add($index, 0) === false)
				return true;
			$this->memcache->delete($index, 0);
			return false;
		}

		public function __unset($index) {
			$this->memcache->delete($index, 0);
		}
	}
