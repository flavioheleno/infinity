<?php

	require_once 'HTML/Template/Sigma.php';

	class TEMPLATE {
		private $log = null;
		private $sigma = null;

		public function __construct(&$log, $path, $cache) {
			$this->log = $log;
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

		public function load_template($template) {
			$res = $this->sigma->loadTemplateFile($template.'.html', true, true);
			if ($res == SIGMA_OK)
				return true;
			$this->log->add($res->getMessage());
			return false;
		}

		public function set($index, $value) {
			$this->sigma->setVariable($index, $value);
		}

		public function add($block, $file) {
			$res = $this->sigma->addBlockFile($block, $block, $file.'.html');
			if ($res == SIGMA_OK)
				return true;
			$this->log->add($res->getMessage());
			return false;
		}

		public function hide($block) {
			$res = $this->sigma->hideBlock($block);
			if ($res == SIGMA_OK)
				return true;
			$this->log->add($res->getMessage());
			return false;
		}

		public function show($block) {
			$res = $this->sigma->touchBlock($block);
			if ($res == SIGMA_OK)
				return true;
			$this->log->add($res->getMessage());
			return false;
		}

		public function parse($block) {
			try {
				$this->sigma->parse($block);
				return true;
			} catch (Exception $e) {
				$this->log->add($e->getMessage());
				return false;
			}
		}

		public function get() {
			try {
				return $this->sigma->get();
			} catch (Exception $e) {
				return $e->getMessage();
			}
		}

	}

?>
