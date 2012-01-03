<?php
/**
* XCache abstraction
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

	class XCACHE {
		private static $instance = null;

		public static function singleton() {
			if ((is_null(self::$instance)) || (!(self::$instance instanceof XCACHE)))
				self::$instance = new XCACHE;
			return self::$instance;
		}

		public function flush() {
			//no operation yet
		}

		public function extended_set($index, $value, $ttl) {
			xcache_set($index, serialize($value), $ttl);
		}

		public function __set($index, $value) {
			xcache_set($index, serialize($value));
		}

		public function __get($index) {
			return unserialize(xcache_get($index));
		}

		public function __isset($index) {
			return xcache_isset($index);
		}

		public function __unset($index) {
			xcache_unset($index);
		}
	}
