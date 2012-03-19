<?php
/**
* HTML creation helper
*
* @version 0.1
* @author Flávio Heleno <flaviohbatista@gmail.com>
* @link http://code.google.com/p/infinity-framework
* @copyright Copyright (c) 2010/2011, Flávio Heleno
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*/

	class HTML {

		const BS_ALERT = 0;
		const BS_BUTTON = 1;
		const BS_CAROUSEL = 2;
		const BS_COLLAPSE = 3;
		const BS_DROPDOWN = 4;
		const BS_MODAL = 5;
		const BS_POPOVER = 6;
		const BS_SCROLLSPY = 7;
		const BS_TAB = 8;
		const BS_TOOLTIP = 9;
		const BS_TRANSITION = 10;
		const BS_TYPEAHEAD = 11;

		private $lang = null;
		private $enc = null;
		private $base = null;
		private $title = null;
		private $description = null;
		private $keywords = null;
		private $favicon = null;
		private $http = array();
		private $meta = array();
		private $css = array();
		private $js = array();
		private $content = array();
		private $path = null;

		public function __construct($lang = 'pt-br', $enc = 'utf-8', array $http = array(), array $meta = array()) {
			$this->lang = $lang;
			$this->enc = $enc;
			foreach ($http as $key => $value)
				$this->http[$key] = $value;
			$this->meta = array(
				'robots' => 'index,follow'
			);
			foreach ($meta as $key => $value)
				$this->meta[$key] = $value;
			$this->path = PATH::singleton();
			$this->add_css('bootstrap.css');
		}

		public function set_base($value) {
			$this->base = $value;
		}

		public function set_title($value) {
			$this->title = $value;
		}

		public function set_description($value) {
			$this->description = $value;
		}

		public function set_keywords($value) {
			$this->keywords = $value;
		}

		public function set_http($index, $value) {
			$this->http[$index] = $value;
		}

		public function set_meta($index, $value) {
			$this->meta[$index] = $value;
		}

		public function set_favicon($file) {
			$this->favicon = $file;
		}

		public function basic_js() {
			if (count($this->js) == 0) {
				$this->add_js('jquery.js');
				$this->add_js('bootstrap.js');
			}
		}

		public function add_js($file) {
			if (!is_array($file))
				$this->js[] = $this->path->relative('js').$file;
			else
				foreach ($file as $item)
					$this->js[] = $this->path->relative('js').$item;
		}

		public function clean_js() {
			unset($this->js);
			$this->js = array();
		}

		public function bootstrap($plugin) {
			$this->basic_js();
			switch ($plugin) {
				case BS_ALERT:
					$this->add_js('bootstrap-alert.js');
					break;
				case BS_BUTTON:
					$this->add_js('bootstrap-button.js');
					break;
				case BS_CAROUSEL:
					$this->add_js('bootstrap-carousel.js');
					break;
				case BS_COLLAPSE:
					$this->add_js('bootstrap-collapse.js');
					break;
				case BS_DROPDOWN:
					$this->add_js('bootstrap-dropdown.js');
					break;
				case BS_MODAL:
					$this->add_js('bootstrap-modal.js');
					break;
				case BS_POPOVER:
					$this->add_js('bootstrap-popover.js');
					break;
				case BS_SCROLLSPY:
					$this->add_js('bootstrap-scrollspy.js');
					break;
				case BS_TAB:
					$this->add_js('bootstrap-tab.js');
					break;
				case BS_TOOLTIP:
					$this->add_js('bootstrap-tooltip.js');
					break;
				case BS_TRANSITION:
					$this->add_js('bootstrap-transition.js');
					break;
				case BS_TYPEAHEAD:
					$this->add_js('bootstrap-typeahead.js');
					break;
			}
		}

		public function add_css($file) {
			if (!is_array($file))
				$this->css[] = $this->path->relative('css').$file;
			else
				foreach ($file as $item)
					$this->css[] = $this->path->relative('css').$item;
		}

		public function clean_css() {
			unset($this->css);
			$this->css = array();
		}

		public function prepend_content($content) {
			array_unshift($this->content, $this->content);
		}

		public function append_content($content) {
			array_push($this->content, $content);
		}

		public function clean_content() {
			unset($this->content);
			$this->content = array();
		}

		public function parse($content, $indent = 0) {
			$content = str_replace("\r", '', $content);
			$tmp = explode("\n", $content);
			$count = count($tmp);
			$bfr = '';
			for ($i = 0; $i < $count; $i++) {
				$tmp[$i] = trim($tmp[$i]);	
				if ($tmp[$i] != '') {
					if (preg_match('/^<[^>]+\/><[^>]+>$/', $tmp[$i])) {
						$bfr .= str_repeat("\t", $indent).$tmp[$i]."\n";
						$indent++;
					} else if ((preg_match('/^<[^>]+\/>$/', $tmp[$i])) || (preg_match('/<[^>]+>.*?<\/[^>]+>$/', $tmp[$i])) || (preg_match('/^<!--.*?-->/', $tmp[$i])))
						$bfr .= str_repeat("\t", $indent).$tmp[$i]."\n";
					else if (preg_match('/^\}[^\{]+\{$/', $tmp[$i]))
						$bfr .= str_repeat("\t", ($indent - 1)).$tmp[$i]."\n";
					else if ((preg_match('/^<\/[^>]+>$/', $tmp[$i])) || (preg_match('/^\}/', $tmp[$i]))) {
						$indent--;
						$bfr .= str_repeat("\t", $indent).$tmp[$i]."\n";
					} else if ((preg_match('/^<[^>]+>$/', $tmp[$i])) || (preg_match('/\{$/', $tmp[$i]))) {
						$bfr .= str_repeat("\t", $indent).$tmp[$i]."\n";
						$indent++;
					} else
						$bfr .= str_repeat("\t", $indent).$tmp[$i]."\n";
				}
			}
			return $bfr;
		}

		public function render() {
			$bfr = '<!DOCTYPE html>'."\n";
			$bfr .= '<html lang="'.$this->lang.'">'."\n";
			$bfr .= '	<head>'."\n";
			$bfr .= '		<meta charset="'.$this->enc.'" />'."\n";
			$this->http = array_unique($this->http);
			foreach ($this->http as $key => $value)
				$bfr .= '		<meta http-equiv="'.$key.'" content="'.$value.'" />'."\n";
			$this->meta = array_unique($this->meta);
			foreach ($this->meta as $key => $value)
				$bfr .= '		<meta name="'.$key.'" content="'.$value.'" />'."\n";
			if ((!is_null($this->description)) && ($this->description))
				$bfr .= '		<meta name="description" content="'.htmlentities($this->description, ENT_QUOTES | ENT_IGNORE, 'UTF-8').'" />'."\n";
			if ((!is_null($this->keywords)) && ($this->keywords))
				$bfr .= '		<meta name="keywords" content="'.htmlentities($this->keywords, ENT_QUOTES | ENT_IGNORE, 'UTF-8').'" />'."\n";
			if ((!is_null($this->title)) && ($this->title))
				$bfr .= '		<title>'.htmlentities($this->title, ENT_QUOTES | ENT_IGNORE, 'UTF-8').'</title>'."\n";
			if ((!is_null($this->base)) && ($this->base))
				$bfr .= '		<base href="'.$this->base.'" />'."\n";
			if ((!is_null($this->favicon)) && ($this->favicon))
				$bfr .= '		<link rel="icon" type="image/x-icon" href="'.$this->favicon.'" />'."\n";
			$this->css = array_unique($this->css);
			foreach ($this->css as $item)
				$bfr .= '		<link rel="stylesheet" type="text/css" href="'.$item.'" />'."\n";
			$bfr .= '		<!--[if lt IE 9]>'."\n";
			$bfr .= '		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>'."\n";
			$bfr .= '		<![endif]-->'."\n";
			$this->js = array_unique($this->js);
			foreach ($this->js as $item)
				$bfr .= '		<script type="text/javascript" src="'.$item.'"></script>'."\n";
			$bfr .= '	</head>'."\n";
			$bfr .= '	<body>'."\n";
			foreach ($this->content as $item)
				$bfr .= $this->parse($item, 2);
			$bfr .= '	</body>'."\n";
			$bfr .= '</html>'."\n";
			return $bfr;
		}

	}
