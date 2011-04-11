<?php

	class CACHE {
		private $name = '';
		private $control = array();

		public function __construct($name = '') {
			$this->name = strtolower($name);
			//ensure that cache dir exists and has the right permissions
			if (!file_exists(__DIR__.'/../cache/')) {
				@mkdir(__DIR__.'/../cache/');
				@chmod(__DIR__.'/../cache/', 777);
			}
			if ((file_exists(__DIR__.'/../cache/control.cache')) && (is_file(__DIR__.'/../cache/control.cache')))
				$this->control = unserialize(file_get_contents(__DIR__.'/../cache/control.cache'));
		}

		public function __destruct() {
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
			if ((file_exists(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html')) && (is_file(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html'))) {
				if ((isset($this->control[$this->name][$id])) && ($this->control[$this->name][$id] > time()))
					return true;
				else
					return false;
			} else
				return false;
		}

		public function add($id, $data, $timeout = 3600) {
			$this->control[$this->name][$id] = (time() + $timeout);
			file_put_contents(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html', $data);
		}

		public function del($id) {
			if (isset($this->control[$this->name][$id]))
				unset($this->control[$this->name][$id]);
			if ((file_exists(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html')) && (is_file(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html')))
				@unlink(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html');
		}

		public function get($id) {
			if ((file_exists(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html')) && (is_file(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html')))
				if ((isset($this->control[$this->name][$id])) && ($this->control[$this->name][$id] > time()))
					echo file_get_contents(__DIR__.'/../cache/'.$this->name.'_'.$id.'.html');
				else
					echo '';
			else
				echo '';
		}

	}

?>
