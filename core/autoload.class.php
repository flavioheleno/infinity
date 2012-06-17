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

spl_autoload_register('AUTOLOAD::load_core');
require __DIR__.'/path.class.php';

class AUTOLOAD {

	public static function load_core($name) {
		$path = PATH::singleton();
		$file = $path->absolute('core').strtolower($name).'.class.php';
		if ((file_exists($file)) && (is_file($file)))
			require $file;
	}

	public static function load_plugin($name) {
		$path = PATH::singleton();
		$name = strtolower($name);
		$file = $path->absolute('plugin').$name.'.class.php';
		if ((file_exists($file)) && (is_file($file)))
			require_once $file;
	}

	public static function load_controller($name) {
		$path = PATH::singleton();
		$file = $path->absolute('app').strtolower($name).'.controller.php';
		if ((file_exists($file)) && (is_file($file))) {
			require $file;
			$name = strtoupper($name).'_CONTROLLER';
			return new $name;
		}
		require $path->absolute('core').'/controller.class.php';
		return new CONTROLLER(strtolower($name));
	}

	public static function load_view($name) {
		$path = PATH::singleton();
		$file = $path->absolute('app').strtolower($name).'.view.php';
		if ((file_exists($file)) && (is_file($file))) {
			require $file;
			$name = strtoupper($name).'_VIEW';
			return new $name;
		}
		return null;
	}

	public static function load_model($name) {
		$path = PATH::singleton();
		$file = $path->absolute('app').strtolower($name).'.model.php';
		if ((file_exists($file)) && (is_file($file))) {
			require $file;
			$name = strtoupper($name).'_MODEL';
			return new $name;
		}
		return null;
	}

}
