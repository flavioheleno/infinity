<?php
/**
* Index file, does all the hard work
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

	require_once __DIR__.'/core/autoload.class.php';

	//creates path object (setting base path)
	$path = PATH::singleton(dirname(__FILE__));

	//creates config object
	$config = CONFIGURATION::singleton();

	if ($config->framework['other']['debug']) {
		ini_set("display_errors",1);
		error_reporting(E_ALL);
	} else {
		ini_set("display_errors",0);
		error_reporting(0);
	}

	//creates log object
	$log = LOG::singleton('infinity.log');

	//adds benchmark time to log file
	if ($config->framework['other']['benchmark'])
		register_shutdown_function(function(&$log, $start) {
			$log->add('Benchmark: '.round(((microtime(true) - $start) * 1000), 2).'ms');
		}, $log, microtime(true));

	//handles request information (routes, variables, etc)
	REQUEST::parse($config, $log, $module, $action);

	$log->add('Module parameter: '.$module);
	$log->add('Action parameter: '.$action);

	//checks if module name is well formed
	if (preg_match('/^[a-z_][a-z0-9_-]*$/i', $module))
		//grabs a new instance of module
		$controller = AUTOLOAD::load_controller($module);
	else
		$controller = null;

	//if the module exists
	if (!is_null($controller)) {
		if ($action == '')
			$action = $controller->default_action;
		//checks if action name is well formed
		if ((preg_match('/^[a-z_][a-z0-9_-]*$/i', $action)) && (substr($action, 0, 2) != '__')) {
			//checks if there is an alias for the given module->action and updates it
			$controller->check_alias($action);
			$log->add('Action alias: '.$action);
			//creates an instance of cache class
			$cache = CACHE::singleton($module, $action);
			//checks if action exists
			if ((method_exists($controller, $action)) && (is_callable(array($controller, $action)))) {
				$log->add('Controller has the action');
				//calls pre-action function
				$controller->pre_action();
				//checks if action is cacheable
				$cacheable = $controller->cacheable($action);
				if ($cacheable === false)
					//calls the controller's action
					$controller->$action();
				else {
					$log->add('Action is cacheable');
					//checks if cache has cached version of action
					if ($cache->has())
						//dispatches cached version
						$cache->get();
					else {
						$log->add('Cache not found or expired');
						//starts output buffering
						ob_start();
						//calls the controller's action
						$controller->$action();
						//updates cache content
						if ($cacheable === true)
							$cache->set(ob_get_contents());
						else
							$cache->set(ob_get_contents(), $cacheable);
						//flushed output buffer
						ob_end_flush();
					}
				}
				//calls pos-action function
				$controller->pos_action();
				//prevents default page to be shown
				exit;
			} else {
				$log->add('Trying to call view\'s action');
				//calls pre-action function
				$controller->pre_action();
				//checks if action is cacheable
				$cacheable = $controller->cacheable($action);
				if ($cacheable === false) {
					//tries to call the controller's action
					if ($controller->$action())
						//prevents default page to be shown
						exit;
				} else {
					$log->add('Action is cacheable');
					//checks if cache has cached version of action
					if ($cache->has()) {
						//dispatches cached version
						$cache->get();
						//prevents default page to be shown
						exit;
					} else {
						$log->add('Cache not found or expired');
						//starts output buffering
						ob_start();
						//calls the controller's action
						if ($controller->$action()) {
							//updates cache content
							if ($cacheable === true)
								$cache->set(ob_get_contents());
							else
								$cache->set(ob_get_contents(), $cacheable);
							//flushed output buffer
							ob_end_flush();
							//prevents default page to be shown
							exit;
						}
					}
				}
			}
		}
	}
	$log->add('Can\'t find '.$module.'->'.$action.', showing default page');
	header('Status: 404 Not Found');
	echo '404 - Not Found'."\n";
