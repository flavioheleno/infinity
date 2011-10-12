<?php
/**
* Base view
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

	abstract class VIEW {
		//module name
		protected $name = '';
		//instance of data class
		protected $data = null;
		//instance of template class
		protected $tpl = null;
		//instance of xhtml class
		protected $xhtml = null;
		//instance of form class
		protected $form = null;
		//instance of log class
		protected $log = null;
		//sets the helpers needed by class
		protected $uses = array();
		//sets the actions that can be cached
		protected $cacheable = array();
		//sets the response content
		protected $response = null;

		//class constructor
		public function __construct($name) {
			$this->name = $name;
			$this->log = LOG::singleton('infinity.log');
			$this->data = DATA::singleton();
			//creates template object
			if (in_array('template', $this->uses))
				$this->tpl = new TEMPLATE($name);
			//creates xhtml object
			if (in_array('xhtml', $this->uses)) {
				$this->xhtml = new XHTML;
				$config = CONFIGURATION::singleton();
				$this->xhtml->set_base('http://'.$config->framework['main']['domain'].$config->framework['main']['base_path']);
				$path = PATH::singleton();
				if ((file_exists($path->get_path('img').'favicon.ico')) && (is_file($path->get_path('img').'favicon.ico')))
					$this->xhtml->set_favicon('img/favicon.ico');
				else if ((file_exists($path->get_path('root').'favicon.ico')) && (is_file($path->get_path('root').'favicon.ico')))
					$this->xhtml->set_favicon('favicon.ico');
			}
			//creates form object
			if (in_array('form', $this->uses)) {
				$this->form = new FORM($name);
				if (!is_null($this->xhtml)) {
					$this->xhtml->add_js(FORM::js());
					$this->xhtml->add_css(FORM::css());
				}
			}
		}

		public function cacheable($action) {
			//if action is just cacheable, with no timeout defined
			if (in_array($action, $this->cacheable))
				return true;
			//if action is cacheable, but has a defined timeout
			if (isset($this->cacheable[$action]))
				return $this->cacheable[$action];
			//action isn't cacheable
			return false;
		}

		protected function render($title, $description = '', $keywords = '') {
			if ((!is_null($this->tpl)) && (!is_null($this->xhtml))) {
				$this->xhtml->set_title($title);
				$this->xhtml->set_description($description);
				$this->xhtml->set_keywords($keywords);
				$this->xhtml->append_content($this->tpl->get());
				$this->response = $this->xhtml->render();
				return true;
			}
			$this->log->add('Trying to render view without Template or XHTML objects');
			return false;
		}

		//dispatches the response
		protected function dispatch() {
			if (!is_null($this->response))
				if (is_array($this->response))
					echo json_encode($this->response);
				else
					echo $this->response;
		}

	}
