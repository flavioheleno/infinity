<?php
/**
* Handles class loading
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

	spl_autoload_register('AUTOLOAD::load');

	class AUTOLOAD {

		public static function load($class) {
			$class = strtolower($class);
			if (preg_match('/^aux_/i', $class)) {
				$class = str_replace('_', '.', $class);
				$file = __DIR__.'/../app/'.$class.'.php';
				if ((file_exists($file)) && (is_file($file)))
					require_once $file;
			} else if ((file_exists(__DIR__.'/'.$class.'.class.php')) && (is_file(__DIR__.'/'.$class.'.class.php')))
				require_once __DIR__.'/'.$class.'.class.php';
		}

		public static function load_plugin($name) {
			$name = strtolower($name);
			$file = __DIR__.'/../plugin/'.$name.'.class.php';
			if ((file_exists($file)) && (is_file($file)))
				require_once $file;
		}

		public static function load_controller($name) {
			$file = __DIR__.'/../app/'.strtolower($name).'.controller.php';
			if ((file_exists($file)) && (is_file($file))) {
				require_once $file;
				$name = strtoupper($name);
				$module = $name.'_CONTROLLER';
				return new $module($name);
			}
			require_once __DIR__.'/controller.class.php';
			return new CONTROLLER(strtoupper($name));
		}

		public static function load_view($name) {
			$file = __DIR__.'/../app/'.strtolower($name).'.view.php';
			if ((file_exists($file)) && (is_file($file))) {
				require_once $file;
				$name = strtoupper($name);
				$module = $name.'_VIEW';
				return new $module($name);
			}
			return null;
		}

		public static function load_model($name) {
			$file = __DIR__.'/../app/'.strtolower($name).'.model.php';
			if ((file_exists($file)) && (is_file($file))) {
				require_once $file;
				$name = strtoupper($name);
				$module = $name.'_MODEL';
				return new $module($name);
			}
			return null;
		}

	}
