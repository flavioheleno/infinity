<?php
/**
* Handles HTTP request
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

	class REQUEST {

		public static function parse(&$module, &$action) {
			//creates config object
			$config = CONFIGURATION::singleton();
			//creates log object
			$log = LOG::singleton();
			//checks if framework is using routing
			if ($config->framework['main']['friendly_url']) {
				$log->add('Using friendly urls ('.$_SERVER['REQUEST_URI'].')');
				self::parse_uri($config, $module, $action);
			} else {
				$log->add('Using default urls ('.$_SERVER['QUERY_STRING'].')');
				self::parse_qs($config, $module, $action);
			}
		}

		private static function parse_rewrite(&$config, &$uri) {
			$config->load_core('rewrite');
			foreach ($config->rewrite as $regex => $rule)
				if (preg_match($regex, $uri)) {
					if (is_array($rule)) {
						if (strtoupper($_SERVER['HTTP_METHOD']) == strtoupper($rule[0])) {
							$uri = preg_replace($regex, $rule[1], $uri);
							break;
						} else {
							if ((isset($rule[3])) && ($rule[3])) {
								header('Location: '.$rule[2]);
								exit;
							} else {
								$uri = preg_replace($regex, $rule[2], $uri);
								break;
							}
						}
					} else {
						$uri = preg_replace($regex, $rule, $uri);
						break;
					}
				}
			$config->unload('rewrite');
		}

		private static function parse_uri(&$config, &$module, &$action) {
			$uri = trim($_SERVER['REQUEST_URI']);
			self::parse_rewrite($config, $uri);
			if (($uri == '/') || ($uri == '')) {
				$module = $config->framework['main']['default_module'];
				$action = '';
			} else {
				if (strpos($uri, '/') === false) {
					$module = $uri;
					$action = '';
				} else {
					$config->load_core('route');
					if (substr($uri, 0, 1) == '/')
						$uri = substr($uri, 1);
					$pieces = explode('/', $uri);
					$module = $pieces[0];
					$action = $pieces[1];
					if (isset($config->route[$module][$action])) {
						foreach ($config->route[$module][$action] as $index => $variable)
							if (isset($pieces[($index + 2)]))
								$_REQUEST[$variable] = $pieces[($index + 2)];
							else
								$_REQUEST[$variable] = null;
					}
					$config->unload('route');
				}
			}
		}

		private static function parse_qs(&$config, &$module, &$action) {
			//defines the module
			if (isset($_REQUEST['m']))
				$module = strtolower($_REQUEST['m']);
			else if (isset($_REQUEST['mod']))
				$module = strtolower($_REQUEST['mod']);
			else if (isset($_REQUEST['module']))
				$module = strtolower($_REQUEST['module']);
			else
				$module = $config->framework['main']['default_module'];
			//defines the action
			if (isset($_REQUEST['a']))
				$action = strtolower($_REQUEST['a']);
			else if (isset($_REQUEST['act']))
				$action = strtolower($_REQUEST['act']);
			else if (isset($_REQUEST['action']))
				$action = strtolower($_REQUEST['action']);
			else
				$action = '';
		}

	}
