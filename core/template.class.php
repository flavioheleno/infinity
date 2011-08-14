<?php

	require_once 'HTML/Template/Sigma.php';

	class TEMPLATE {
		private $name = '';
		private $log = null;
		private $sigma = null;

		public function __construct($name, $path, $cache) {
			$this->name = strtolower($name);
			$this->log = LOG::singleton('infinity.log');
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

		public function load_template($id, $fullid = false) {
			if ($fullid)
				$res = $this->sigma->loadTemplateFile($id.'.html', true, true);
			else
				$res = $this->sigma->loadTemplateFile($this->name.'_'.$id.'.html', true, true);
			if ($res == SIGMA_OK)
				return true;
			$this->log->add($res->getMessage());
			return false;
		}

		public function block_parse($data) {
			foreach ($data as $block => $items) {
				if (is_array($items))
					foreach ($items as $item) {
						$this->set($block, $item);
						$this->parse($block);
					}
				else
					$this->set($block, $items);
			}
		}

		public function set($index, $value) {
			if (is_array($value))
				foreach ($value as $key => $item)
					$this->set($index.'_'.$key, $item);
			else
				$this->sigma->setVariable($index, $value);
		}

		public function add($block, $id, $fullid = false) {
			if ($fullid)
				$res = $this->sigma->addBlockFile($block, $block, $id.'.html');
			else
				$res = $this->sigma->addBlockFile($block, $block, $this->name.'_'.$id.'.html');
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
