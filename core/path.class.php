<?php
/**
* Handles path creation
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

class PATH {
	private static $instance = null;
	//stores base path
	private $path = null;
	//stores all relative paths
	private $location = array(
		'app' => 'app/',
		'cache' => 'cache/',
		'cfg' => array(
			'app' => 'cfg/app/',
			'core' => 'cfg/core/',
			'form' => 'cfg/form/'
		),
		'core' => 'core/',
		'css' => 'css/',
		'img' => 'img/',
		'js' => 'js/',
		'log' => 'log/',
		'mail' => 'mail/',
		'plugin' => 'plugin/',
		'template' => array(
			'root' => 'tpl/',
			'cache' => 'tpl/cache/'
		),
		'root' => '',
		'worker' => 'worker/'
	);

	public function __construct() {
		$this->path = __DIR__.'/../';
	}

	public function relative($parent, $child = false) {
		$parent = strtolower($parent);
		if ($child === false) {
			if (isset($this->location[$parent]))
				return $this->location[$parent];
		} else {
			$child = strtolower($child);
			if (isset($this->location[$parent][$child]))
				return $this->location[$parent][$child];
		}
		return false;
	}

	public function absolute($parent, $child = false) {
		$path = $this->relative($parent, $child);
		if ($path === false)
			return false;
		return realpath($this->path.$path).'/';
	}

	public static function singleton() {
		if ((is_null(self::$instance)) || (!(self::$instance instanceof PATH)))
			self::$instance = new PATH;
		return self::$instance;
	}

}
