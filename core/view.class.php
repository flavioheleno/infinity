<?php

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
		public function __construct($name, &$log) {
			$this->name = $name;
			$this->log = $log;
			$this->data = DATA::singleton();
			//creates template object
			if (in_array('template', $this->uses))
				$this->tpl = new TEMPLATE($log, $name, __DIR__.'/../tpl', __DIR__.'/../tpl/cache');
			//creates xhtml object
			if (in_array('xhtml', $this->uses))
				$this->xhtml = new XHTML;
			//creates form object
			if (in_array('form', $this->uses))
				$this->form = new FORM($log, $name);
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

?>
