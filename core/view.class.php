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
		//instance of cache class
		protected $cache = null;
		//instance of log class
		protected $log = null;
		//sets the helpers needed by class
		protected $uses = array();

		//class constructor
		public function __construct($name, &$log) {
			$this->name = $name;
			$this->log = $log;
			$this->data = DATA::singleton();
			//creates template object
			if (in_array('template', $this->uses))
				$this->tpl = new TEMPLATE(__DIR__.'/../tpl', __DIR__.'/../tpl/cache');
			//creates xhtml object
			if (in_array('xhtml', $this->uses))
				$this->xhtml = new XHTML;
			//creates form object
			if (in_array('form', $this->uses))
				$this->form = new FORM($name);
			//creates cache object
			if (in_array('cache', $this->uses))
				$this->cache = new CACHE($name);
		}

		protected function display($title, $description = '', $keywords = '') {
			if ((!is_null($this->tpl)) && (!is_null($this->xhtml))) {
				$this->xhtml->set_title($title);
				$this->xhtml->set_description($description);
				$this->xhtml->set_keywords($keywords);
				$this->xhtml->append_content($this->tpl->get());
				return $this->xhtml->render();
			}
			return null;
		}

	}

?>
