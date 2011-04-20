<?php

	require_once __DIR__.'/../cfg/core/framework.config.php';

	class CACHE {
		private $name = '';
		private $control = array();
		private $enabled = false;

		public function __construct($name = '') {
			global $_INFINITY_CFG;
			//base name for cache control
			$this->name = strtolower($name);
			if (isset($_INFINITY_CFG['cache']['enabled']))
				$this->enabled = $_INFINITY_CFG['cache']['enabled'];
			if ($this->enabled) {
				//ensure that cache dir exists and has the right permissions
				if (!file_exists(__DIR__.'/../cache/')) {
					@mkdir(__DIR__.'/../cache/');
					@chmod(__DIR__.'/../cache/', 777);
				}
				//if cache control is found, loads it, else, cleans cache dir
				if ((file_exists(__DIR__.'/../cache/control.cache')) && (is_file(__DIR__.'/../cache/control.cache')))
					$this->control = unserialize(file_get_contents(__DIR__.'/../cache/control.cache'));
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

		public function has($id) {
			if ($this->enabled)
				if ((file_exists(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html')) && (is_file(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html'))) {
					if ((isset($this->control[$this->name][$id])) && ($this->control[$this->name][$id] > time()))
						return true;
					return false;
				}
			return false;
		}

		public function add($id, $data, $timeout = 3600) {
			if ($this->enabled) {
				$this->control[$this->name][$id] = (time() + $timeout);
				file_put_contents(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html', $data);
			}
		}

		public function del($id) {
			if ($this->enabled) {
				if (isset($this->control[$this->name][$id]))
					unset($this->control[$this->name][$id]);
				if ((file_exists(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html')) && (is_file(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html')))
					@unlink(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html');
			}
		}

		public function get($id) {
			if ($this->enabled)
				if ((file_exists(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html')) && (is_file(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html'))) {
					if ((isset($this->control[$this->name][$id])) && ($this->control[$this->name][$id] > time()))
						return file_get_contents(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html');
					return null;
				}
			return null;
		}

	}

?>
