<?php

	require_once __DIR__.'/autoload.class.php';

	class CONTROLLER {
		//module name
		protected $name = '';
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
		//instance of msg class
		protected $msg = null;
		//web path
		protected $path = '/';

		//class constructor
		public function __construct($name, $session, $email, $msg, $path) {
			$this->name = $name;
			$this->view = AUTOLOAD::loadView($name, __DIR__.'/../tpl', __DIR__.'/../tpl/cache', $msg);
			$this->model = AUTOLOAD::loadModel($name);
			$this->aux = AUTOLOAD::loadAuxController();
			$this->session = $session;
			$this->email = $email;
			$this->msg = $msg;
			$this->path = $path;
		}

		//creates a 302 HTTP redirect
		public function redirect($url) {
			if (strtolower(substr($url, 0, 7)) == 'http://')
				header('Location: '.$url);
			else
				header('Location: '.$this->path.$url);
			exit(0);
		}

		//cleans every form variable
		public function cleanEnviroment(array &$env) {
			foreach ($env as $k => $v)
				if (preg_match('/^(text_|password_|textarea_|checkbox_|radio_|select_|hidden_|submit_|reset_)/', $k))
					unset($env[$k]);
		}

		//direct calls view function
		public function __call($function, $arguments) {
			if (!is_null($this->view)) {
				if (is_callable(array($this->view, $function)))
					$this->view->$function($arguments);
				else
					$this->view->error($arguments);
				return true;
			} else
				return false;
		}

	}

?>
