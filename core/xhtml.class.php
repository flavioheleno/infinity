<?php

	class XHTML {
		private $lang = null;
		private $enc = null;
		private $title = null;
		private $description = null;
		private $keywords = null;
		private $favicon = null;
		private $http = array();
		private $meta = array();
		private $css = array();
		private $js = array();
		private $content = array();

		public function __construct($lang = 'pt-br', $enc = 'iso-8859-1', array $http = array(), array $meta = array()) {
			$this->lang = $lang;
			$this->enc = $enc;
			$this->http = array(
				'Content-Language' => $this->lang,
				'Content-Type' => 'text/html; charset='.$this->enc,
				'Cache-Control' => 'public'
			);
			foreach ($http as $key => $value)
				$this->http[$key] = $value;
			$this->meta = array(
				'robots' => 'index,follow',
				'copyright' => 'none',
				'rating' => 'general'
			);
			foreach ($meta as $key => $value)
				$this->meta[$key] = $value;
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

		public function parse($content, $indent = 0) {
			$content = str_replace("\r", '', $content);
			$tmp = explode("\n", $content);
			$count = count($tmp);
			for ($i = 0; $i < $count; $i++) {
				$tmp[$i] = trim($tmp[$i]);	
				if ((preg_match('/^<[^>]+\/>$/', $tmp[$i])) || (preg_match('/<[^>]+>.*?<\/[^>]+>$/', $tmp[$i])))
					echo str_repeat("\t", $indent).$tmp[$i]."\n";
				else if (preg_match('/^<\/[^>]+>$/', $tmp[$i])) {
					$indent--;
					echo str_repeat("\t", $indent).$tmp[$i]."\n";
				} else if (preg_match('/^<[^>]+>$/', $tmp[$i])) {
					echo str_repeat("\t", $indent).$tmp[$i]."\n";
					$indent++;
				} else
					echo str_repeat("\t", $indent).$tmp[$i]."\n";
			}
		}

		public function render() {
			echo '<?xml version="1.0" encoding="'.$this->enc.'"?>'."\n";
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
			echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$this->lang.'" lang="'.$this->lang.'">'."\n";
			echo '	<head>'."\n";
			$this->http = array_unique($this->http);
			foreach ($this->http as $key => $value)
				echo '		<meta http-equiv="'.$key.'" content="'.$value.'" />'."\n";
			$this->meta = array_unique($this->meta);
			foreach ($this->meta as $key => $value)
				echo '		<meta name="'.$key.'" content="'.$value.'" xml:lang="'.$this->lang.'" lang="'.$this->lang.'" />'."\n";
			if ((!is_null($this->description)) && ($this->description))
				echo '		<meta name="description" content="'.$this->description.'" xml:lang="'.$this->lang.'" lang="'.$this->lang.'" />'."\n";
			if ((!is_null($this->keywords)) && ($this->keywords))
				echo '		<meta name="keywords" content="'.$this->keywords.'" xml:lang="'.$this->lang.'" lang="'.$this->lang.'" />'."\n";
			if ((!is_null($this->title)) && ($this->title))
				echo '		<title>'.$this->title.'</title>'."\n";
			if ((!is_null($this->favicon)) && ($this->favicon))
				echo '		<link rel="shortcut icon" type="image/x-icon" href="'.$this->favicon.'" />'."\n";
			$this->css = array_unique($this->css);
			foreach ($this->css as $item)
				echo '		<link rel="stylesheet" type="text/css" href="'.$item.'" />'."\n";
			$this->js = array_unique($this->js);
			foreach ($this->js as $item)
				echo '		<script type="text/javascript" src="'.$item.'"></script>'."\n";
			echo '	</head>'."\n";
			echo '	<body>'."\n";
			foreach ($this->content as $item)
				$this->parse($item, 2);
			echo '	</body>'."\n";
			echo '</html>'."\n";
		}

	}
?>
