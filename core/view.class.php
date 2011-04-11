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
			if (in_array('xhtml', $this->uses))
				$this->xhtml = new XHTML;
		}

		protected function display($title, $description = '', $keywords = '') {
			if ((!is_null($this->tpl)) && (!is_null($this->xhtml))) {
				$this->xhtml->set_title($title);
				$this->xhtml->set_description($description);
				$this->xhtml->set_keywords($keywords);
				$this->xhtml->append_content($this->tpl->get());
				$this->xhtml->render();
			}
		}

	}

?>
