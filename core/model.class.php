<?php
/**
* Base model
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

abstract class MODEL {

	//lazy loading
	public function __get($index) {
		switch ($index) {
			case 'query':
				$config = CONFIGURATION::singleton();
				$config->load_core('db');
				$this->query = new QUERY($config->db);
				$config->unload('db');
				return $this->query;
			case 'data':
				$this->data = DATA::singleton();
				return $this->data;
			case 'log':
				$this->log = LOG::singleton();
				return $this->log;
			case 'secure':
				$this->secure = new SECURE;
				return $this->secure;
			default:
				return null;
		}
	}

}
