<?php
/**
* Base model
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

	abstract class MODEL {
		//module name
		protected $name = '';
		//instance of data class
		protected $data = null;
		//instance of query class
		protected $query = null;
		//instance of secure class;
		protected $secure = null;
		//instance of log class
		protected $log = null;
		//validation rules for data used in this model
		protected $rules = array();
		//field values used in this model
		protected $field = array();
		//sets the helpers needed by class
		protected $uses = array();

		//class constructor
		public function __construct($name) {
			$this->name = $name;
			$this->log = LOG::singleton('infinity.log');
			$this->data = DATA::singleton();
			$config = CONFIGURATION::singleton();
			$config->load_core('db');
			$this->query = new QUERY($config->db);
			//creates secure object
			if (in_array('secure', $this->uses))
				$this->secure = new SECURE;
		}

		protected static function output_date(&$data, $format = 'd/m/y') {
			if (preg_match('/^([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+):([0-9]+)$/', $data, $matches)) {
				$format = str_ireplace('d', $matches[3], $format);
				$format = str_ireplace('m', $matches[2], $format);
				$format = str_ireplace('y', $matches[1], $format);
				$format = str_ireplace('h', $matches[4], $format);
				$format = str_ireplace('i', $matches[5], $format);
				$format = str_ireplace('s', $matches[6], $format);
			} else if (preg_match('/^([0-9]+)-([0-9]+)-([0-9]+)$/', $data, $matches)) {
				$format = str_ireplace('d', $matches[3], $format);
				$format = str_ireplace('m', $matches[2], $format);
				$format = str_ireplace('y', $matches[1], $format);
			} else if (preg_match('/^([0-9]+):([0-9]+):([0-9]+)$/', $data, $matches)) {
				$format = str_ireplace('h', $matches[1], $format);
				$format = str_ireplace('i', $matches[2], $format);
				$format = str_ireplace('s', $matches[3], $format);
			} else
				$format = '';
			$data = $format;
			return $format;
		}

		protected static function output_html(&$data) {
			$data = nl2br(htmlentities(utf8_decode($data)));
			return $data;
		}

		public function load($id, $fullid = false) {
			$path = PATH::singleton();
			if ($fullid)
				$file = $path->get_path('cfg', 'form').strtolower($id).'.xml';
			else
				$file = $path->get_path('cfg', 'form').strtolower($this->name).'_'.$id.'.xml';
			if ((file_exists($file)) && (is_file($file))) {
				$src = file_get_contents($file);
				$xml = new SimpleXMLElement($src);
				if ($xml === false) {
					$this->log->add('Invalid XML file ('.$file.')');
					return false;
				}
				foreach ($xml->fields->field as $item) {
					if (isset($item->rule)) {
						$this->rules[(string)$item['id']] = array();
						foreach ($item->rule as $rule) {
							if (isset($rule['id'])) {
								if (isset($rule['value']))
									$this->rules[(string)$item['id']][(string)$rule['id']] = (string)$rule['value'];
								else
									$this->rules[(string)$item['id']][] = (string)$rule['id'];
							}
						}
					}
					$key = (string)$item['type'].'_'.(string)$item['id'];
					if (isset($_REQUEST[$key]))
						$this->field[(string)$item['id']] = $_REQUEST[$key];
					else
						$this->field[(string)$item['id']] = false;
				}
				return true;
			}
			$this->log->add('File not found: '.$file);
			return false;
		}

		public function unload() {
			$this->rules = array();
			$this->field = array();
		}

		public function sanitize() {
			foreach ($this->field as $field => &$value)
				if (isset($this->rules[$field]))
					VALIDATOR::sanitize($value, $this->rules[$field]);
		}

		public function validate() {
			$valid = true;
			foreach ($this->field as $field => $value)
				if (isset($this->rules[$field]))
					$valid &= VALIDATOR::check($value, $this->rules[$field]);
			return $valid;
		}

	}
