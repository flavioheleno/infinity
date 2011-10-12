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

		public static function parse(&$config, &$log, &$module, &$action) {
			//checks if framework is using routing
			if ($config->framework['main']['route']) {
				$log->add('Using routing');
				self::parse_route($config, $module, $action);
			} else {
				$log->add('Using query string');
				self::parse_qs($module, $action);
			}
		}

		private static function parse_qs(&$config, &$module, &$action) {
			$config->load_core('route');
			$qs = trim($_SERVER['QUERY_STRING']);
			if ($qs == '') {
				$module = $config->framework['main']['default_module'];
				$action = '';
			} else {
				if (strpos($qs, '/') === false) {
					$module = $qs;
					$action = '';
				} else {
					$pieces = explode('/', $qs);
					$module = $pieces[0];
					$action = $pieces[1];
					if (isset($config->route[$module][$action])) {
						foreach ($config->route[$module][$action] as $index => $variable)
							if (isset($pieces[($index + 2)]))
								$_REQUEST[$variable] = $pieces[($index + 2)];
							else
								$_REQUEST[$variable] = null;
					}
				}
			}
		}

		private static function parse_route(&$module, &$action) {
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
