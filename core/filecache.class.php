<?php
/**
* FileCache abstraction
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

	class FILECACHE {
		private static $instance = null;
		private $path = null;
		private $control = array();

		public function __construct() {
			//path controller
			$path = PATH::singleton();
			$this->path = $path->get_path('cache');
			//ensure that cache dir exists and has the right permissions
			if (!file_exists($this->path)) {
				@mkdir($this->path);
				@chmod($this->path, 777);
			}
			//if cache control is found, loads it, else, cleans cache dir
			$file = $this->path.'control.cache';
			if ((file_exists($file)) && (is_file($file)))
				$this->control = unserialize(file_get_contents($file));
			else
				$this->flush();
		}

		public function __destruct() {
			//saves cache control
			file_put_contents($this->path.'control.cache', serialize($this->control));
		}

		public static function singleton() {
			if (!(self::$instance instanceof FILECACHE))
				self::$instance = new FILECACHE;
			return self::$instance;
		}

		public function flush() {
			if (file_exists($this->path)) {
				$ids = scandir($this->path);
				foreach ($ids as $id)
					if (preg_match('/\.(html|cache)$/i', $id))
						@unlink($this->path.$id);
			}
		}

		public function extended_set($index, $value, $ttl) {
			$this->control[$index] = (time() + $ttl);
			file_put_contents($this->path.$index.'.html', $data);
		}

		public function __set($index, $value) {
			$this->control[$index] = (time() + 3600);
			file_put_contents($this->path.$index.'.html', $data);
		}

		public function __get($index) {
			$file = $this->path.$index.'.html';
			if ((file_exists($file)) && (is_file($file))) {
				if ((isset($this->control[$index])) && ($this->control[$index] > time()))
					return file_get_contents($file);
			}
			return '';
		}

		public function __isset($index) {
			$file = $this->path.$index.'.html';
			if ((file_exists($file)) && (is_file($file))) {
				if ((isset($this->control[$index])) && ($this->control[$index] > time()))
					return true;
			}
			return false;
		}

		public function __unset($index) {
			if (isset($this->control[$index]))
				unset($this->control[$index]);
			$file = $this->path.$index.'.html';
			if ((file_exists($file)) && (is_file($file)))
				@unlink($file);
		}
	}
