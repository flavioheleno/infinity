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
		private $lang = null;
		private $enc = null;
		private $base = null;
		private $title = null;
		private $description = null;
		private $keywords = null;
		private $favicon = null;
		private $html5 = false;
		private $http = array();
		private $meta = array();
		private $css = array();
		private $js = array();
		private $content = array();

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

		public function add_js($file) {
			if (!is_array($file))
				$this->js[] = $file;
			else
				foreach ($file as $item)
					$this->js[] = $item;
		}

		public function clean_js() {
			unset($this->js);
			$this->js = array();
		}

		public function add_css($file) {
			if (!is_array($file))
				$this->css[] = $file;
			else
				foreach ($file as $item)
					$this->css[] = $item;
		}

		public function clean_css() {
			unset($this->css);
			$this->css = array();
		}

		public function add_anchor($href, $text, array $prop = array(), $pre = '', $pos = '') {
			$tmp = $pre."\n";
			$tmp .= '<a href="'.$href.'"';
			foreach ($prop as $key => $value)
				$tmp .= ' '.$key.'="'.$value.'"';
			$tmp .= '>'.$text.'</a>'."\n";
			$tmp .= $pos."\n";
			$this->append_content($tmp);
		}

		public function add_image($src, $alt = '', array $prop = array(), $pre = '', $pos = '') {
			$tmp = $pre."\n";
			$tmp .= '<img src="'.$src.'" alt="'.$alt.'" ';
			foreach ($prop as $key => $value)
				$tmp .= $key.'="'.$value.'" ';
			$tmp .= '/>'."\n";
			$tmp .= $pos."\n";
			$this->append_content($tmp);
		}

		public function add_div($content, $id = null, $class = null) {
			$tmp = '<div';
			if (!is_null($id))
				$tmp .= ' id="'.$id.'"';
			if (!is_null($class))
				$tmp .= ' class="'.$class.'"';
			$tmp .= '>'."\n";
			$tmp .= $content."\n";
			$tmp .= '</div>'."\n";
			$this->append_content($tmp);
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

		public function set_html5() {
			$this->html5 = true;
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
			if ($this->html5) {
				$bfr = '<!DOCTYPE html>'."\n";
				$bfr .= '<html>'."\n";
			} else {
				$bfr = '<?xml version="1.0" encoding="'.$this->enc.'" ?>'."\n";
				$bfr .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
				$bfr .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$this->lang.'" lang="'.$this->lang.'">'."\n";
			}
			$bfr .= '	<head>'."\n";
			if ($this->html5) {
				$bfr .= '		<!--[if lt IE 9]>'."\n";
				$bfr .= '		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>'."\n";
				$bfr .= '		<![endif]-->'."\n";
				$bfr .= '		<meta charset="'.$this->enc.'" />'."\n";
			} else
				$this->http['Content-Type'] = 'text/html; charset='.$this->enc;
			$this->http = array_unique($this->http);
			foreach ($this->http as $key => $value)
				$bfr .= '		<meta http-equiv="'.$key.'" content="'.$value.'" />'."\n";
			$this->meta = array_unique($this->meta);
			foreach ($this->meta as $key => $value) {
				$bfr .= '		<meta name="'.$key.'" content="'.$value;
				if (!$this->html5)
					$bfr .= '" xml:lang="'.$this->lang.'" lang="'.$this->lang;
				$bfr .= '" />'."\n";
			}
			if ((!is_null($this->description)) && ($this->description)) {
				$bfr .= '		<meta name="description" content="'.$this->description;
				if (!$this->html5)
					$bfr .= '" xml:lang="'.$this->lang.'" lang="'.$this->lang;
				$bfr .= '" />'."\n";
			}
			if ((!is_null($this->keywords)) && ($this->keywords)) {
				$bfr .= '		<meta name="keywords" content="'.$this->keywords;
				if (!$this->html5)
					$bfr .= '" xml:lang="'.$this->lang.'" lang="'.$this->lang;
				$bfr .= '" />'."\n";
			}
			if ((!is_null($this->title)) && ($this->title))
				$bfr .= '		<title>'.$this->title.'</title>'."\n";
			if ((!is_null($this->base)) && ($this->base))
				$bfr .= '		<base href="'.$this->base.'" />'."\n";
			if ((!is_null($this->favicon)) && ($this->favicon))
				$bfr .= '		<link rel="icon" type="image/x-icon" href="'.$this->favicon.'" />'."\n";
			$this->css = array_unique($this->css);
			foreach ($this->css as $item)
				$bfr .= '		<link rel="stylesheet" type="text/css" href="'.$item.'" />'."\n";
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
