<?php

	class XCACHE {
		private static $instance = null;

		public static function singleton() {
			if (!(self::$instance instanceof XCACHE))
				self::$instance = new XCACHE;
			return self::$instance;
		}

		public function extended_set($index, $value, $ttl) {
			xcache_set($index, $value, $ttl);
		}

		public function __set($index, $value) {
			xcache_set($index, $value);
		}

		public function __get($index) {
			return xcache_get($index);
		}

		public function __isset($index) {
			return xcache_isset($index);
		}

		public function __unset($index) {
			xcache_unset($index);
		}
	}

?>
