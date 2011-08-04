<?php

	class MCACHE {
		private static $instance = null;
		private $memcache = null;

		public function __construct() {
			$this->memcache = new Memcache;
			$this->memcache->connect('localhost', 11211);
		}

		public static function singleton() {
			if (!(self::$instance instanceof MCACHE))
				self::$instance = new MCACHE;
			return self::$instance;
		}

		public function extended_set($index, $value, $ttl) {
			$this->memcache->set($index, $value, false, $ttl);
		}

		public function __set($index, $value) {
			$this->memcache->set($index, $value);
		}

		public function __get($index) {
			return $this->memcache->get($index);
		}

		public function __isset($index) {
			if ($this->memcache->add($index, 0) === false)
				return true;
			$this->memcache->delete($index);
			return false;
		}

		public function __unset($index) {
			$this->memcache->delete($index);
		}
	}

?>
