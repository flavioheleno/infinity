<?php

	require_once 'HTML/Template/Sigma.php';

	class TEMPLATE {
		private $sigma = null;

		public function __construct($path, $cache) {
			$this->sigma = new HTML_Template_Sigma($path, $cache);
			$this->sigma->setCallbackFunction('url_create', function () {
				if (func_num_args() > 0) {
					$par = array();
					foreach (func_get_args() as $arg)
						if (strpos($arg, '=') !== false) {
							$tmp = explode('=', $arg);
							$par[$tmp[0]] = $tmp[1];
						} else
							$par[] = $arg;
					return URL::create($par, true);
				}
				return '';
			});
			$this->sigma->setCallbackFunction('url_create_raw', function () {
				if (func_num_args() > 0) {
					$par = array();
					foreach (func_get_args() as $arg)
						if (strpos($arg, '=') !== false) {
							$tmp = explode('=', $arg);
							$par[$tmp[0]] = $tmp[1];
						} else
							$par[] = $arg;
					return URL::create($par, false);
				}
				return '';
			});
		}

		public function load_file_template($template) {
			$this->sigma->loadTemplateFile($template.'.html', true, true);
		}

		public function set($index, $value) {
			$this->sigma->setVariable($index, $value);
		}

		public function add($block, $file) {
			$this->sigma->addBlockFile($block, $block, $file.'.html');
		}

		public function hide($block) {
			$this->sigma->hideBlock($block);
		}

		public function show($block) {
			$this->sigma->touchBlock($block);
		}

		public function parse($block) {
			$this->sigma->parse($block);
		}

		public function get() {
			return $this->sigma->get();
		}

	}

?>
