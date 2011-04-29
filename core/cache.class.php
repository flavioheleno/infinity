<?php

	AUTOLOAD::require_core_config('framework');

	class CACHE {
		private $module = '';
		private $action = '';
		private $control = array();
		private $enabled = false;
		private $signature = true;

		public function __construct($module = '', $action = '', $signature = true) {
			global $_INFINITY_CFG;
			//base name for cache control
			$this->module = strtolower($module);
			$this->action = strtolower($action);
			if (isset($_INFINITY_CFG['cache']['enabled']))
				$this->enabled = $_INFINITY_CFG['cache']['enabled'];
			$this->signature = $signature;
			if ($this->enabled) {
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
			}
		}

		public function del() {
			if ($this->enabled) {
				if (isset($this->control[$this->module][$this->action]))
					unset($this->control[$this->module][$this->action]);
				$file = __DIR__.'/../cache/'.$this->module.'_'.$this->action.'.html';
				if ((file_exists($file)) && (is_file($file)))
					@unlink($file);
			}
		}

		public function get() {
			if ($this->enabled)
				$file = __DIR__.'/../cache/'.$this->module.'_'.$this->action.'.html';
				if ((file_exists($file)) && (is_file($file))) {
					if ((isset($this->control[$this->module][$this->action])) && ($this->control[$this->module][$this->action] > time()))
						echo file_get_contents($file);
				}
		}

	}

?>
