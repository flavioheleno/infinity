<?php
/**
* cURL abstraction
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

	class CURL {
		private $handler = null;
		private $referer = '';
		private $range = array();
		private $fresh = false;
		private $debug = false;

		public function __construct() {
			$this->handler = curl_init();
		}

		public function __destruct() {
			curl_close($this->handler);
		}

		public function status() {
			return ($this->handler !== false);
		}

		public function last_error() {
			return curl_error($this->handler);
		}

		public function set_referer($value) {
			$this->referer = $value;
		}

		public function set_range(array $value) {
			$this->range = $value;
		}

		public function set_fresh($value) {
			$this->fresh = $value;
		}

		public function set_debug($value) {
			$this->debug = $value;
		}

		private function load_opt($url) {
			$opt = array(
				CURLOPT_AUTOREFERER => true,
				CURLOPT_FAILONERROR => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT => 120,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_URL => $url,
				CURLOPT_USERAGENT => 'php-'.phpversion()
			);
			if ($this->referer != '')
				$opt[CURLOPT_REFERER] = $this->referer;
			if (count($this->range))
				$opt[CURLOPT_RANGE] = implode(',', $this->range);
			if ($this->fresh)
				$opt[CURLOPT_FRESH_CONNECT] = true;
			if ($this->debug)
				$opt[CURLOPT_HEADER] = true;
			return $opt;
		}

		public function get($url, $new_session = false) {
			if ($this->handler === false)
				return false;
			$opt = $this->load_opt($url);
			$opt[CURLOPT_HTTPGET] = true;
			if ($new_session)
				$opt[CURLOPT_COOKIESESSION] = true;
			curl_setopt_array($this->handler, $opt);
			return curl_exec($this->handler);
		}

		public function mget(array $urls, $new_session = false) {
			$handler = curl_multi_init();
			$handlers = array();
			foreach ($urls as $url) {
				$tmp = curl_init();
				$opt = $this->load_opt($url);
				if ($new_session)
					$opt[CURLOPT_COOKIESESSION] = true;
				curl_setopt_array($tmp, $opt);
				unset($opt);
				$handlers[] = $tmp;
				curl_multi_add_handle($handler, $tmp);
				unset($tmp);
			}
			$active = null;
			do
				$status = curl_multi_exec($handler, $active);
			while ($status == CURLM_CALL_MULTI_PERFORM);
			while (($active) && ($status == CURLM_OK)) {
				if (curl_multi_select($handler) != -1)
					do
						$status = curl_multi_exec($handler, $active);
					while ($status == CURLM_CALL_MULTI_PERFORM);
			}
			$ret = array();
			foreach ($handlers as $hnd) {
				$ret[] = curl_multi_getcontent($hnd);
				curl_multi_remove_handle($handler, $hnd);
				curl_close($hnd);
			}
			curl_multi_close($handler);
			return $ret;
		}

		public function head($url, $new_session = false) {
			if ($this->handler === false)
				return false;
			$opt = $this->load_opt($url);
			$opt[CURLOPT_HEADER] = true;
			$opt[CURLOPT_NOBODY] = true;
			if ($new_session)
				$opt[CURLOPT_COOKIESESSION] = true;
			curl_setopt_array($this->handler, $opt);
			return curl_exec($this->handler);
		}

		public function post($url, array $data, $new_session = false) {
			if ($this->handler === false)
				return false;
			$opt = $this->load_opt($url);
			$opt[CURLOPT_POST] = true;
			$opt[CURLOPT_POSTFIELDS] = http_build_query($data);
			if ($new_session)
				$opt[CURLOPT_COOKIESESSION] = true;
			curl_setopt_array($this->handler, $opt);
			return curl_exec($this->handler);
		}

		public function upload($url, array $data, $new_session = false) {
			if ($this->handler === false)
				return false;
			$opt = $this->load_opt($url);
			$opt[CURLOPT_UPLOAD] = true;
			$opt[CURLOPT_POSTFIELDS] = http_build_query($data);
			if ($new_session)
				$opt[CURLOPT_COOKIESESSION] = true;
			curl_setopt_array($this->handler, $opt);
			return curl_exec($this->handler);
		}

	}
