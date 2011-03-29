<?php

	require_once __DIR__.'/template.class.php';
	require_once __DIR__.'/form.class.php';
	require_once __DIR__.'/xhtml.class.php';
	require_once __DIR__.'/url.class.php';
	require_once __DIR__.'/log.class.php';
	require_once __DIR__.'/msg.class.php';

	abstract class VIEW {
		//module name
		protected $name = '';
		//instance of template class
		protected $tpl = null;
		//instance of form class
		protected $form = null;
		//instance of auxiliar class
		protected $aux = null;
		//instance of xhtml class
		protected $xhtml = null;
		//instance of url class
		protected $url = null;
		//instance of log class
		protected $log = null;
		//sets the helpers needed by class
		protected $uses = array();

		//class constructor
		public function __construct($name, &$log) {
			$this->name = $name;
			$this->log = $log;
			//creates template object
			if (in_array('template', $this->uses))
				$this->tpl = new TEMPLATE(__DIR__.'/../tpl', __DIR__.'/../tpl/cache');
			//creates form object
			if (in_array('form', $this->uses))
				$this->form = new FORM;
			//creates view's auxiliar object
			if (in_array('aux', $this->uses))
				$this->aux = AUTOLOAD::load_aux_view();
			//creates xhtml object
			if (in_array('xhtml', $this->uses))
				$this->xhtml = new XHTML;
			//creates url object
			if (in_array('url', $this->uses))
				$this->url = new URL;
		}

		protected function display($title, $description = '', $keywords = '') {
			if ((!is_null($this->tpl)) && (!is_null($this->xhtml))) {
				if (basename($_SERVER['SCRIPT_NAME']) == 'index.php')
					$this->tpl->set('base-link', dirname($_SERVER['SCRIPT_NAME']).'?'.$_SERVER['QUERY_STRING'].'&amp;');
				else
					$this->tpl->set('base-link', $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'].'&amp;');
				$this->xhtml->set_title($title);
				$this->xhtml->set_description($description);
				$this->xhtml->set_keywords($keywords);
				$this->xhtml->append_content($this->tpl->get());
				$this->xhtml->render();
			}
		}

		//default called method when no action is defined
		public abstract function index(array $env);

		//error method, called when not existent action is called
		public abstract function error(array $env);

	}

?>
