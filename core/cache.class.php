<?php
/**
* Output cache
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

	class CACHE {
		private static $instance = null;
		private $log = null;
		private $module = '';
		private $action = '';
		private $control = array();
		private $enabled = false;
		private $signature = true;

		public function __construct($module = '', $action = '', $signature = true) {
			$this->log = LOG::singleton('infinity.log');
			$config = CONFIGURATION::singleton();
			//base name for cache control
			$this->module = strtolower($module);
			$this->action = strtolower($action);
			if (isset($config->framework['cache']['enabled']))
				$this->enabled = $config->framework['cache']['enabled'];
			$this->signature = $signature;
			if ($this->enabled) {
				$this->log->add('Cache is enabled');
				//ensure that cache dir exists and has the right permissions
				if (!file_exists(__DIR__.'/../cache/')) {
					@mkdir(__DIR__.'/../cache/');
					@chmod(__DIR__.'/../cache/', 777);
				}
				//if cache control is found, loads it, else, cleans cache dir
				$file = __DIR__.'/../cache/control.cache';
				if ((file_exists($file)) && (is_file($file)))
					$this->control = unserialize(file_get_contents($file));
				else
					CACHE::clean();
			}
		}

		//singleton method - avoids the creation of more than one instance
		public static function singleton() {
			//checks if there is an instance of class, if not, create it
			if (!(self::$instance instanceof CACHE))
				self::$instance = new CACHE;
			return self::$instance;
		}

		public function __destruct() {
			if ($this->enabled)
				//saves cache control
				file_put_contents(__DIR__.'/../cache/control.cache', serialize($this->control));
		}

		public static function clean() {
			if (file_exists(__DIR__.'/../cache/')) {
				$ids = scandir(__DIR__.'/../cache/');
				foreach ($ids as $id)
					if (preg_match('/\.(html|cache)$/i', $id))
						@unlink(__DIR__.'/../cache/'.$id);
			}
		}

		public function has() {
			if ($this->enabled) {
				$file = __DIR__.'/../cache/'.$this->module.'_'.$this->action.'.html';
				if ((file_exists($file)) && (is_file($file))) {
					if ((isset($this->control[$this->module][$this->action])) && ($this->control[$this->module][$this->action] > time()))
						return true;
					return false;
				}
				$this->log->add('Cache not found for '.$this->module.'->'.$this->action);
			}
			return false;
		}

		public function set($data, $timeout = 3600) {
			if ($this->enabled) {
				$this->control[$this->module][$this->action] = (time() + $timeout);
				if ($this->signature) {
					$data .= "\n";
					$data .= '<!-- cached at '.date('H:i:s d/m/Y').' -->';
				}
				file_put_contents(__DIR__.'/../cache/'.$this->module.'_'.$this->action.'.html', $data);
				$this->log->add('Creating cache for '.$this->module.'->'.$this->action);
			}
		}

		public function del() {
			if ($this->enabled) {
				if (isset($this->control[$this->module][$this->action]))
					unset($this->control[$this->module][$this->action]);
				$file = __DIR__.'/../cache/'.$this->module.'_'.$this->action.'.html';
				if ((file_exists($file)) && (is_file($file)))
					@unlink($file);
				$this->log->add('Removing cache for '.$this->module.'->'.$this->action);
			}
		}

		public function get() {
			if ($this->enabled)
				$file = __DIR__.'/../cache/'.$this->module.'_'.$this->action.'.html';
				if ((file_exists($file)) && (is_file($file))) {
					if ((isset($this->control[$this->module][$this->action])) && ($this->control[$this->module][$this->action] > time()))
						echo file_get_contents($file);
				}
				$this->log->add('Getting cache for '.$this->module.'->'.$this->action);
		}

	}
