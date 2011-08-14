<?php

	class PRIVILEGE {

		public static function check($value, $base) {
			return (($value & $base) == $base);
		}

		public static function set($value, $base) {
			return ($value | $base);
		}

	}
