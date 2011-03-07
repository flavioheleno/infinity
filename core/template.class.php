<?php

	require_once 'HTML/Template/Sigma.php';

	class TEMPLATE {
		private $sigma = null;

		public function __construct($path, $cache) {
			$this->sigma = new HTML_Template_Sigma($path, $cache);
		}

		public function load_file_template($template) {
			$this->sigma->loadTemplateFile($template, true, true);
		}

		public function set($index, $value) {
			$this->sigma->setVariable($index, $value);
		}

		public function add($block, $file) {
			$this->sigma->addBlockFile($block, $block, $file);
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
