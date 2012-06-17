<?php
/**
* Semaphore plugin
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

class LOCK {
	private $handler = null;

	public function __construct($name) {
		$this->handler = fopen(sys_get_temp_dir().'/'.$name.'.lock', 'w');
	}

	public function __destruct() {
		if ($this->handler)
			fclose($this->handler);
	}

	public function lock($block = false) {
		if (!$this->handler)
			return false;
		if ($block)
			return flock($this->handler, LOCK_EX);
		return flock($this->handler, LOCK_EX | LOCK_NB);
	}

	public function unlock() {
		if ($this->handler)
			flock($this->handler, LOCK_UN);
	}

}
