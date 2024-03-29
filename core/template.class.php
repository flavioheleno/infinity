<?php
/**
* Basic Template abstraction
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

require 'HTML/Template/Sigma.php';

class TEMPLATE {
	private $name = '';
	private $log = null;
	private $sigma = null;

	public function __construct($name) {
		$this->name = strtolower($name);
		$this->log = LOG::singleton();
		$path = PATH::singleton();
		$this->sigma = new HTML_Template_Sigma($path->absolute('template', 'root'), $path->absolute('template', 'cache'));
	}

	public function load($id, $fullid = false) {
		if ($fullid)
			$res = $this->sigma->loadTemplateFile($id.'.html', true, true);
		else
			$res = $this->sigma->loadTemplateFile($this->name.'_'.$id.'.html', true, true);
		if ((!($res instanceof PEAR_Error)) && ($res == SIGMA_OK))
			return true;
		$this->log->add($res->getMessage());
		return false;
	}


	public function set($index, $value, $escape = true) {
		if (is_array($value))
			foreach ($value as $key => $item)
				$this->set($index.'_'.$key, $item, $escape);
		else {
			if ($escape)
				$this->sigma->setVariable($index, htmlentities($value, ENT_QUOTES | ENT_IGNORE, 'UTF-8'));
			else
				$this->sigma->setVariable($index, $value);
		}
	}

	public function block_set(array $data, $escape = true) {
		foreach ($data as $key => $value)
			$this->set($key, $value, $escape);
	}

	public function add($block, $id, $fullid = false) {
		if ($fullid)
			$res = $this->sigma->addBlockFile($block, $block, $id.'.html');
		else
			$res = $this->sigma->addBlockFile($block, $block, $this->name.'_'.$id.'.html');
		if ((!($res instanceof PEAR_Error)) && ($res == SIGMA_OK))
			return true;
		$this->log->add($res->getMessage());
		return false;
	}

	public function hide($block) {
		$res = $this->sigma->hideBlock($block);
		if ((!($res instanceof PEAR_Error)) && ($res == SIGMA_OK))
			return true;
		$this->log->add($res->getMessage());
		return false;
	}

	public function show($block) {
		$res = $this->sigma->touchBlock($block);
		if ((!($res instanceof PEAR_Error)) && ($res == SIGMA_OK))
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

	public function block_parse(array $data) {
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

	public function get() {
		try {
			return $this->sigma->get();
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

}
