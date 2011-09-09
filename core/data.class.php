<?php
/**
* Handles data exchange between classes (controllers/models/views)
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

	class DATA {
		//holds class instances for singleton
		private static $instance = null;
		//holds data values
		private $data = array();

		//singleton method - avoids the creation of more than one instance per data control
		public static function singleton() {
			//checks if there is an instance of class, if not, create it
			if (!(self::$instance instanceof DATA))
				self::$instance = new DATA;
			return self::$instance;
		}

		//sets data item value
		public function __set($index, $value) {
			$this->data[$index] = $value;
		}

		//gets data item value
		public function __get($index) {
			if (isset($this->data[$index]))
				return $this->data[$index];
			return false;
		}

		//checks if data item is set
		public function __isset($index) {
			return isset($this->data[$index]);
		}

		//unset data item
		public function __unset($index) {
			unset($this->data[$index]);
		}

	}
