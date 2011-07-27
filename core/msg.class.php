<?php

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
			$config = CONFIGURATION::singleton();
			$config->load_core('msg');
			if (isset($config->msg[$type][$code]))
				return $config->msg[$type][$code];
			else
				return array();
		}

	}

?>
