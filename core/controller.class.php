<?php
/**
* Base controller
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

class CONTROLLER {
	//controller's name
	protected $name;
	//web path
	protected $path = '/';
	//domain
	protected $domain = '';
	//sets the aliases for this controller
	protected $alias = array();
	//sets the response content
	protected $response = null;
	//sets the controller's default action
	public $default_action = 'index';

	//class constructor
	public function __construct($name = '') {
		if ($name == '')
			$this->name = str_replace('_CONTROLLER', '', get_class($this));
		else
			$this->name = $name;

		$config = CONFIGURATION::singleton();
		$this->domain = $config->framework['main']['domain'];
		$this->path = $config->framework['main']['base_path'];
	}

	//changes an alias for a given action
	public function check_alias(&$action) {
		if (isset($this->alias[$action]))
			$action = $this->alias[$action];
	}

	//creates a 302 HTTP redirect
	protected function redirect($url = '') {
		$this->log->add('Redirecting to '.$url);
		if (($url != '') && (preg_match('/^https?:\/\//', $url)))
			header('Location: '.$url);
		else {
			if ((substr($this->path, -1) == '/') && (substr($url, 0, 1) == '/'))
				$url = substr($url, 1);
			header('Location: '.$this->path.$url);
		}
		exit;
	}

	//creates an HTTP error
	protected function http_error($code, $message) {
		header('Status: '.$code.' '.$message);
		exit;
	}

	//checks if action is cacheable
	public function cacheable($action) {
		if (isset($this->cacheable)) {
			//if action is just cacheable, with no timeout defined
			if (in_array($action, $this->cacheable))
				return true;
			//if action is cacheable, but has a defined timeout
			if (isset($this->cacheable[$action]))
				return $this->cacheable[$action];
		}
		if (is_null($this->view))
			return false;
		return $this->view->cacheable($action);
	}

	//returns real client ip address
	protected function get_ip_address() {
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key)
			if (array_key_exists($key, $_SERVER) === true)
				foreach (explode(',', $_SERVER[$key]) as $ip)
					if (filter_var($ip, FILTER_VALIDATE_IP) !== false)
						return $ip;
	}

	//checks if request is made via an ajax request
	protected function is_ajax() {
		if ((isset($_SERVER['HTTP_X_REQUESTED_WITH'])) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))
			return true;
		return false;
	}

	//checks if request is made via https
	protected function is_secure() {
		if ((isset($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] == 'on'))
			return true;
		return false;
	}

	//redirects the request to https scheme
	protected function make_secure() {
		$this->redirect("https://{$this->domain}{$_SERVER['REQUEST_URI']}");
	}

	//checks if request comes from expected referer
	protected function check_referer() {
		if ((isset($_SERVER['HTTP_REFERER'])) && (!preg_match('/^https?:\/\/'.quotemeta($this->domain).'/', $_SERVER['HTTP_REFERER'])))
			return false;
		return true;
	}

	//dispatches the response
	protected function dispatch($exit = false) {
		if (!is_null($this->response))
			if (is_array($this->response)) {
				header('Content-Type: application/json; charset=UTF-8');
				echo json_encode($this->response);
			} else {
				header('Content-Type: text/html; charset=UTF-8');
				echo $this->response;
			}
		if ($exit)
			exit;
	}

	//direct calls view function and calls pre/pos action
	public function __call($function, $arguments) {
		if (($function == 'pre_action') || ($function == 'pos_action'))
			if ((method_exists($this, $function)) && (is_callable(array($this, $function)))) {
				$this->log->add('Calling '.$function);
				$this->$function();
			}
		if (is_null($this->view))
			return false;
		if (is_callable(array($this->view, $function))) {
			$this->view->$function($arguments);
			return true;
		} else if (is_callable(array($this->view, 'error'))) {
			$this->view->error($arguments);
			return true;
		}
		return false;
	}

	//lazy loading
	public function __get($index) {
		switch ($index) {
			case 'log':
				$this->log = LOG::singleton();
				return $this->log;
			case 'data':
				$this->data = DATA::singleton();
				return $this->data;
			case 'input':
				$this->input = INPUT::singleton();
				return $this->input;
			case 'view':
				$this->view = AUTOLOAD::load_view($this->name);
				return $this->view;
			case 'model':
				$this->model = AUTOLOAD::load_model($this->name);
				return $this->model;
			case 'session':
				$this->session = SESSION::singleton();
				return $this->session;
			case 'cookie':
				$this->cookie = COOKIE::singleton();
				return $this->cookie;
			case 'xcache':
				$this->xcache = XCACHE::singleton();
				return $this->xcache;
			case 'mcache':
				$this->mcache = MCACHE::singleton();
				return $this->mcache;
			case 'filecache':
				$this->filecache = FILECACHE::singleton();
				return $this->filecache;
			default:
				return null;
		}
	}
}
