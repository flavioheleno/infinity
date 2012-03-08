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
		//instance of data class
		protected $data = null;
		//instance of query class
		protected $query = null;
		//instance of secure class;
		protected $secure = null;
		//instance of log class
		protected $log = null;
		//sets the helpers needed by class
		protected $uses = array();

		//class constructor
		public function __construct($name) {
			$config = CONFIGURATION::singleton();
			$config->load_core('db');
			$this->query = new QUERY($config->db);
			//creates data object
			if (in_array('data', $this->uses))
				$this->data = DATA::singleton();
			//creates log object
			if (in_array('log', $this->uses))
				$this->log = LOG::singleton();
			//creates secure object
			if (in_array('secure', $this->uses))
				$this->secure = new SECURE;
		}

	}
