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
		private $driver = null;
		private $log = null;
		private $module = '';
		private $action = '';
		private $enabled = false;
		private $signature = true;

		const DRIVER_FILE = 0x00;
		const DRIVER_MEMCACHE = 0x01;
		const DRIVER_XCACHE = 0x02;

		public function __construct($module = '', $action = '', $signature = true) {
			$this->log = LOG::singleton();
			$config = CONFIGURATION::singleton();
			//base name for cache control
			$this->module = strtolower($module);
			$this->action = strtolower($action);
			if (isset($config->framework['cache']['enabled']))
				$this->enabled = $config->framework['cache']['enabled'];
			$this->signature = $signature;
			if ($this->enabled) {
				$this->log->add('Cache is enabled');
				$this->setup_driver($config->framework['cache']['driver']);
			}
		}

		private function setup_driver($option) {
			switch (strtolower($option)) {
				case 'memcache':
					$this->driver = MCACHE::singleton();
					break;
				case 'xcache':
					$this->driver = XCACHE::singleton();
					break;
				case 'file':
				default:
					$this->driver = FILECACHE::singleton();
					break;
			}
		}

		//singleton method - avoids the creation of more than one instance
		public static function singleton($module = '', $action = '', $signature = true) {
			//checks if there is an instance of class, if not, create it
			if ((is_null(self::$instance)) || (!(self::$instance instanceof CACHE)))
				self::$instance = new CACHE($module, $action, $signature);
			return self::$instance;
		}

		public function clean() {
			$this->driver->flush();
		}

		public function has() {
			if ($this->enabled)
				return isset($this->driver->{$this->module.'_'.$this->action});
			return false;
		}

		public function set($data, $timeout = 3600) {
			if ($this->enabled) {
				if ($this->signature) {
					$data .= "\n";
					$data .= '<!-- cached at '.date('H:i:s d/m/Y').' -->';
				}
				$this->driver->extended_set($this->module.'_'.$this->action, $data, $timeout);
				$this->log->add('Creating cache for '.$this->module.'->'.$this->action);
			}
		}

		public function del() {
			if ($this->enabled) {
				unset($this->driver->{$this->module.'_'.$this->action});
				$this->log->add('Removing cache for '.$this->module.'->'.$this->action);
			}
		}

		public function get() {
			if ($this->enabled) {
				echo $this->driver->{$this->module.'_'.$this->action};
				$this->log->add('Getting cache for '.$this->module.'->'.$this->action);
			}
		}

	}
