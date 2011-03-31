<?php

	require_once __DIR__.'/autoload.class.php';
	require_once __DIR__.'/data.class.php';
	require_once __DIR__.'/session.class.php';
	require_once __DIR__.'/email.class.php';
	require_once __DIR__.'/privilege.class.php';
	require_once __DIR__.'/../cfg/core/framework.config.php';

	class CONTROLLER {
		//module name
		protected $name = '';
		//instance of data class
		protected $data = null;
		//instance of view class
		protected $view = null;
		//instance of model class
		protected $model = null;
		//instance of auxiliar class
		protected $aux = null;
		//instance of session class
		protected $session = null;
		//instance of email class
		protected $email = null;
		//instance of log class
		protected $log = null;
		//web path
		protected $path = '/';
		//sets the aliases for this controller
		protected $alias = array();
		//sets the helpers needed by class
		protected $uses = array('view');
		//sets the controller's default action
		public $default_action = 'index';

		//class constructor
		public function __construct($name, &$log) {
			global $_INFINITY_CFG;
			$this->name = $name;
			$this->path = $_INFINITY_CFG['base_path'];
			$this->log = $log;
			$this->data = DATA::singleton();
			//creates view object
			if (in_array('view', $this->uses))
				$this->view = AUTOLOAD::load_view($name, $log);
			//creates model object
			if (in_array('model', $this->uses))
				$this->model = AUTOLOAD::load_model($name, $log);
			//creates controller's auxiliar object
			if (in_array('aux', $this->uses))
				$this->aux = AUTOLOAD::load_aux_controller();
			//creates session helper
			if (in_array('session', $this->uses))
				$this->session = SESSION::singleton(true);
			//creates email object
			if (in_array('email', $this->uses))
				$this->email = new EMAIL;
		}

		//changes an alias for a given action
		public function check_alias(&$action) {
			if (isset($this->alias[$action]))
				$action = $this->alias[$action];
		}

		//creates a 302 HTTP redirect
		public function redirect($url = '') {
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

		//direct calls view function
		public function __call($function, $arguments) {
			if (!is_null($this->view)) {
				if (is_callable(array($this->view, $function))) {
					$this->view->$function($arguments);
					return true;
				} else if (is_callable(array($this->view, 'error'))) {
					$this->view->error($arguments);
					return true;
				} else
					return false;
			} else
				return false;
		}

	}

?>
