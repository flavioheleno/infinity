<?php
/**
* User data validation
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

	class VALIDATOR {

		public static function cleanup($value) {
			$a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
			$b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
			$value = utf8_decode($value);
			$value = strtr($value, utf8_decode($a), $b);
			return utf8_encode($value);
		}

		public static function sanitize(&$value, $rules) {
			foreach ($rules as $rule)
				if (is_array($rule)) {
					$k = key($rule);
					$v = current($rule);
				} else
					$k = $rule;
				switch ($k) {
					case 'alphanumeric':
						$value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
						break;
					case 'number':
					case 'min':
					case 'max':
					case 'range':
						$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
						break;
					case 'decimal':
						$value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, (FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND));
						break;
					case 'string':
					case 'minlength':
					case 'maxlength':
					case 'rangelength':
						$value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
						break;
					case 'email':
						$value = filter_var($value, FILTER_SANITIZE_EMAIL);
						break;
					case 'url':
						$value = filter_var($value, FILTER_SANITIZE_URL);
						break;
					case 'date':
						$value = preg_replace('/[^0-9\/]+/', '', $value);
						break;
				}
		}

		public static function check($value, $rules) {
			$valid = true;
			$item = true;
			//$value = self::cleanup($value);
			foreach ($rules as $rule) {
				if (is_array($rule)) {
					$k = key($rule);
					$v = current($rule);
				} else
					$k = $rule;
				switch ($k) {
					case 'required':
						$item = (boolean)($value != '');
						break;
					case 'alphanumeric':
						$item = (boolean)preg_match('/^[0-9a-z _-]+$/i', $value);
						break;
					case 'number':
						$item = filter_var($value, FILTER_VALIDATE_INT);
						break;
					case 'decimal':
						$item = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
						break;
					case 'string':
						$item = filter_var($value, FILTER_VALIDATE_STRING);
						break;
					case 'email':
						$item = filter_var($value, FILTER_VALIDATE_EMAIL);
						break;
					case 'url':
						$item = filter_var($value, FILTER_VALIDATE_URL);
						break;
					case 'date':
						$item = (boolean)preg_match('/^(0[1-9]|1[0-9]|2[0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[1-2][0-9]{3}$/', $value);
						break;
					case 'min':
						$item = (boolean)($value >= intval($v));
						break;
					case 'max':
						$item = (boolean)($value <= intval($v));
						break;
					case 'range':
						$item = filter_var($value, FILTER_VALIDATE_INT, array('min_range' => $v[0], 'max_range' => $v[1]));
						break;
					case 'minlength':
						$item = (boolean)(strlen($value) >= intval($v));
						break;
					case 'maxlength':
						$item = (boolean)(strlen($value) <= intval($v));
						break;
					case 'rangelength':
						$item = filter_var(strlen($value), FILTER_VALIDATE_INT, array('min_range' => $v[0], 'max_range' => $v[1]));$item = filter_var($value, FILTER_VALIDATE_INT, array('min_range' => $v[0], 'max_range' => $v[1]));
						break;
				}
				if ($item === false)
					$valid = false;
			}
			return $valid;
		}

	}
