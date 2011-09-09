<?php
/**
* Message handling
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
