<?php
/**
* RAM Queue using Beanstalkd
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

	class QUEUE {
		private $beanstalk = null;

		public function __construct() {
			$this->beanstalk = new Beanstalk;
			$this->beanstalk->addserver('localhost', 11300, 1);
		}

		public function put($queue, $workload, $raw = false) {
			$this->beanstalk->use($queue);
			if ($raw)
				return $this->beanstalk->put($queue, $workload);
			return $this->beanstalk->put($queue, serialize($workload));
		}

		public function get($queue, $raw = false) {
			$this->beanstalk->watch($queue);
			$job = $this->beanstalk->reserve($queue);
			if (!isset($job['id']))
				return false;
			$this->beanstalk->delete($job['id'], $queue);
			if ($raw)
				return $job['data'];
			return unserialize($job['data']);
		}

	}
