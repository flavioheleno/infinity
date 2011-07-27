<?php

	class CONTROLLER {
		//module name
		protected $name = '';
		//instance of data class
		protected $data = null;
		//instance of view class
		protected $view = null;
		//instance of model class
		protected $model = null;
		//instance of session class
		protected $session = null;
		//instance of cookie class
		protected $cookie = null;
		//instance of log class
		protected $log = null;
		//instance of config class
		protected $config = null;
		//web path
		protected $path = '/';
		//sets the aliases for this controller
		protected $alias = array();
		//sets the helpers needed by class
		protected $uses = array('view');
		//sets the response content
		protected $response = null;
		//sets the controller's default action
		public $default_action = 'index';

		//class constructor
		public function __construct($name) {
			$this->config = CONFIGURATION::singleton();
			$this->log = LOG::singleton('infinity.log');
			$this->name = $name;
			$this->path = $this->config->framework['base_path'];
			$this->data = DATA::singleton();
			//creates view object
			if (in_array('view', $this->uses))
				$this->view = AUTOLOAD::load_view($name);
			//creates model object
			if (in_array('model', $this->uses))
				$this->model = AUTOLOAD::load_model($name);
			//creates session helper
			if (in_array('session', $this->uses))
				$this->session = SESSION::singleton();
			//creates cookie helper
			if (in_array('cookie', $this->uses))
				$this->cookie = COOKIE::singleton();
		}

		//changes an alias for a given action
		public function check_alias(&$action) {
			if (isset($this->alias[$action]))
				$action = $this->alias[$action];
		}

		//creates a 302 HTTP redirect
		protected function redirect($url = '') {
			$this->log->add('Redirecting to '.$url);
			if (($url != '') && (strtolower(substr($url, 0, 7)) == 'http://'))
				header('Location: '.$url);
			else
				header('Location: '.$this->path.$url);
			exit;
		}

		//cleans every form variable
		public static function clean_enviroment() {
			foreach ($_REQUEST as $key => $value)
				if (preg_match('/^(text_|password_|textarea_|checkbox_|radio_|select_|hidden_|submit_|reset_)/', $key))
					unset($_REQUEST[$key]);
		}

		public function cacheable($action) {
			if (is_null($this->view))
				return false;
			return $this->view->cacheable($action);
		}

		//checks if request comes from expected referer
		protected function check_referer() {
			if ((isset($_SERVER['HTTP_REFERER'])) && (!preg_match('/^http:\/\/'.$this->config->framework['domain'].'/', $_SERVER['HTTP_REFERER'])))
				return false;
			return true;
		}

		//dispatches the response
		protected function dispatch() {
			if (!is_null($this->response))
				if (is_array($this->response)) {
					header('Content-Type: application/json');
					echo json_encode($this->response);
				} else
					echo $this->response;
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

	}

?>
