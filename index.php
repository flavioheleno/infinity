<?php

	if (!defined('__DIR__'))
		define('__DIR__', dirname(__FILE__));

	require_once __DIR__.'/core/autoload.class.php';

	//creates config object
	$config = CONFIGURATION::singleton();

	//creates log object
	$log = LOG::singleton('infinity.log');

	//adds benchmark time to log file
	if ($config->framework['benchmark'])
		register_shutdown_function(function(&$log, $start) {
			$log->add('Benchmark: '.round(((microtime(true) - $start) * 1000), 2).'ms');
		}, $log, microtime(true));

	if ($config->framework['route']) {
		$log->add('Using routing');
		$config->load_core('route');
		$qs = trim($_SERVER['QUERY_STRING']);
		if ($qs == '') {
			$module = $config->framework['default_module'];
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
	} else {
		$log->add('Using query string');
		//defines the module
		if (isset($_REQUEST['m']))
			$module = strtolower($_REQUEST['m']);
		else if (isset($_REQUEST['mod']))
			$module = strtolower($_REQUEST['mod']);
		else if (isset($_REQUEST['module']))
			$module = strtolower($_REQUEST['module']);
		else
			$module = $config->framework['default_module'];
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

	$log->add('Module parameter: '.$module);
	$log->add('Action parameter: '.$action);



	//checks if module name is well formed
	if (preg_match('/^[a-z_][a-z0-9_-]+$/i', $module))
		//grabs a new instance of module
		$controller = AUTOLOAD::load_controller($module);
	else
		$controller = null;

	//if the module exists
	if (!is_null($controller)) {
		if ($action == '')
			$action = $controller->default_action;
		//checks if action name is well formed
		if ((preg_match('/^[a-z_][a-z0-9_-]+$/i', $action)) && (substr($action, 0, 2) != '__')) {
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
