<?php
/**
* User data validation and sanitization
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

	class INPUT {

		const BOOL = 0;
		const INT = 1;
		const INT_MIN = 2;
		const INT_MAX = 3;
		const INT_RANGE = 4;
		const FLOAT = 5;
		const FLOAT_MIN = 6;
		const FLOAT_MAX = 7;
		const FLOAT_RANGE = 8;
		const STRING = 9;
		const STRING_NOHTML = 10;
		const STRING_MINLEN = 11;
		const STRING_MAXLEN = 12;
		const STRING_RANGELEN = 13;
		const EMAIL = 14;
		const URL = 15;
		const DATE = 16;
		const IP = 17;
		const REGEX = 18;

		//holds class instances for singleton
		private static $instance = null;
		private $data = array();

		//singleton method - avoids the creation of more than one instance per input
		public static function singleton() {
			//checks if there is an instance of class, if not, create it
			if ((is_null(self::$instance)) || (!(self::$instance instanceof INPUT)))
				self::$instance = new INPUT;
			return self::$instance;
		}

		public function __construct() {
			$this->data = $_REQUEST;
		}

		public function __set($index, $value) {
			$this->data[$index] = $value;
		}

		public function __get($index) {
			return $this->data[$index];
		}

		//checks if an index exists in _REQUEST variable
		public function has($index) {
			if ((isset($this->data[$index])) && (trim($this->data[$index]) != ''))
				return true;
			return false;
		}

		public function has_array(array $list, &$failed = array()) {
			$ret = true;
			foreach ($list as $item)
				if (!$this->has($item)) {
					$failed[] = $item;
					$ret = false;
				}
			return $ret;
		}

		//cleans and checks if an index is valid in _REQUEST variable
		public function check($index, $type, $param = null) {
			if ($this->has($index)) {
				$this->sanitize($this->data[$index], $type);
				return ($this->validate($this->data[$index], $type, $param) !== false);
			}
			return false;
		}

		public function check_array(array $list, &$failed = array()) {
			$ret = true;
			foreach ($list as $item => $prop)
				if (is_array($prop)) {
					if (!$this->check($item, $prop['type'], $prop['param'])) {
						$failed[] = $item;
						$ret = false;
					}
				} else {
					if (!$this->check($item, $prop)) {
						$failed[] = $item;
						$ret = false;
					}
				}
			return $ret;
		}

		public function clean($index, $type, $default = null) {
			if ($this->has($index)) {
				$this->sanitize($this->data[$index], $type);
				return $this->data[$index];
			}
			return $default;
		}

		private function sanitize(&$value, $type) {
			switch ($type) {
				case self::BOOL:
					$value = preg_replace('/[^tf01]+/i', '', $value);
					break;
				case self::INT:
				case self::INT_MIN:
				case self::INT_MAX:
				case self::INT_RANGE:
					$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
					break;
				case self::FLOAT:
				case self::FLOAT_MIN:
				case self::FLOAT_MAX:
				case self::FLOAT_RANGE:
					$value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, (FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND));
					$value = str_replace('.', '', $value);
					$value = str_replace(',', '.', $value);
					$value = floatval($value);
					break;
				case self::STRING_NOHTML:
					$value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);	
					$value = strip_tags($value);
					break;
				case self::STRING:
				case self::STRING_MINLEN:
				case self::STRING_MAXLEN:
				case self::STRING_RANGELEN:
					$value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
					break;
				case self::EMAIL:
					$value = filter_var($value, FILTER_SANITIZE_EMAIL);
					break;
				case self::URL:
					$value = filter_var($value, FILTER_SANITIZE_URL);
					break;
				case self::DATE:
					$value = preg_replace('/[^0-9\/]+/', '', $value);
					break;
				case self::IP:
					$value = preg_replace('/[^0-9\.\/]+/', '', $value);
					break;
			}
		}

		private function validate($value, $type, $param = null) {
			switch ($type) {
				case self::BOOL:
					return filter_var($value, FILTER_VALIDATE_BOOLEAN);
				case self::INT:
					return filter_var($value, FILTER_VALIDATE_INT);
				case self::INT_MIN:
					$options = array(
						'options' => array(
							'min_range' => intval($param)
						)
					);
					return filter_var($value, FILTER_VALIDATE_INT, $options);
				case self::INT_MAX:
					$options = array(
						'options' => array(
							'max_range' => intval($param)
						)
					);
					return filter_var($value, FILTER_VALIDATE_INT, $options);
				case self::INT_RANGE:
					$options = array(
						'options' => array(
							'min_range' => intval($param[0]),
							'max_range' => intval($param[1])
						)
					);
					return filter_var($value, FILTER_VALIDATE_INT, $options);
				case self::FLOAT:
					return filter_var($value, FILTER_VALIDATE_FLOAT, (FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND));
				case self::FLOAT_MIN:
					return ((filter_var($value, FILTER_VALIDATE_FLOAT, (FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND))) && ($value >= floatval($param)));
				case self::FLOAT_MAX:
					return ((filter_var($value, FILTER_VALIDATE_FLOAT, (FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND))) && ($value <= floatval($param)));
				case self::FLOAT_RANGE:
					if (filter_var($value, FILTER_VALIDATE_INT, (FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND)))
						return (($value >= floatval($param[0])) && ($value <= floatval($param[1])));
					return false;
				case self::STRING:
				case self::STRING_NOHTML:
					return true;
				case self::STRING_MINLEN:
					return (strlen($value) >= intval($param));
				case self::STRING_MAXLEN:
					return (strlen($value) <= intval($param));
				case self::STRING_RANGELEN:
					$len = strlen($value);
					return (($len >= intval($param[0])) && ($len <= intval($param[1])));
				case self::EMAIL:
					return filter_var($value, FILTER_VALIDATE_EMAIL);
				case self::URL:
					return filter_var($value, FILTER_VALIDATE_URL);
				case self::DATE:
					$options = array(
						'options' => array(
							'regexp' => '/^(0[1-9]|1[0-9]|2[0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[1-2][0-9]{3}$/'
						)
					);
					return filter_var($value, FILTER_VALIDATE_REGEXP, $options);
				case self::IP:
					return filter_var($value, FILTER_VALIDATE_IP);
				case self::REGEX:
					if (is_null($param))
						return false;
					$options = array(
						'options' => array(
							'regexp' => $param
						)
					);
					return filter_var($value, FILTER_VALIDATE_REGEXP, $options);
			}
		}

	}
