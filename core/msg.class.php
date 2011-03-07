<?php

	require_once __DIR__.'/../cfg/core/msg.config.php';

	class MSG {
		const ERR = 0x01;
		const SUC = 0x02;
		const WAR = 0x03;

		public static function json($id, $code, $type = 'raw', $action = '') {
			$ret = array(
				'id' => $id,
				'code' => $code,
				'msg' => self::retrieve($id, $code)
			);
			return json_encode($ret);
		}

		public static function retrieve($id, $code) {
			global $_INFINITY_MSG;
			switch ($id) {
				case self::ERR:
					$type = 'ERR';
					break;
				case self::SUC:
					$type = 'SUC';
					break;
				case self::WAR:
					$type = 'WAR';
					break;
				default:
					$type = '';
					
			}
			$code = intval($code);
			if (isset($_INFINITY_MSG[$type][$code]))
				return $_INFINITY_MSG[$type][$code];
			else
				return array();
		}

	}

?>
