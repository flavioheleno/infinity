<?php

	require_once __DIR__.'/../cfg/core/msg.config.php';

	class MSG {

		public static function page($name) {
			switch ($name) {
				case 'default':
					if (file_exists(__DIR__.'/../tpl/core/default.html'))
						echo file_get_contents(__DIR__.'/../tpl/core/default.html');
					break;
				default:
					if ((preg_match('/^[a-z0-9_-]+$/i', $name)) && (file_exists(__DIR__.'/../tpl/app/default/'.$name.'.html')))
						echo file_get_contents(__DIR__.'/../tpl/app/default/'.$name.'.html');
					else
						self::def('default');
			}
		}

		public static function json($id, $code, $type = 'raw', $action = '') {
			$ret = array(
				'id' => $id,
				'code' => $code,
				'msg' => self::retrieve($id, $code)
			);
			return json_encode($ret);
		}

		public static function encode($id, $code) {
			return (($id << 8) | $code);
		}

		public static function decode($value, &$id, &$code) {
			$id = (($value & 0xFF00) >> 8);
			$code = ($value & 0x00FF);
		}

		public static function retrieve($id, $code) {
			global $_INFINITY_MSG;
			if (isset($_INFINITY_MSG[$id][$code]))
				return $_INFINITY_MSG[$id][$code];
			else
				return array();
		}

	}

?>
