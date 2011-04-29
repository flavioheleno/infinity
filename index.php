<?php

	if (!defined('__DIR__'))
		define('__DIR__', dirname(__FILE__));

	require_once __DIR__.'/core/autoload.class.php';
	require_once __DIR__.'/cfg/core/framework.config.php';

	global $_INFINITY_CFG;

	//creates log object
	$log = LOG::singleton('infinity.log');

	//adds benchmark time to log file
	if ($_INFINITY_CFG['benchmark'])
		register_shutdown_function(function(&$log, $start) {
			$log->add('Benchmark: '.round(((microtime(true) - $start) * 1000), 2));
		}, $log, microtime(true));

	if ($_INFINITY_CFG['route']) {
		require_once __DIR__.'/cfg/core/route.config.php';
		global $_INFINITY_ROUTE;
		$qs = trim($_SERVER['QUERY_STRING']);
		if ($qs == '') {
			$module = $_INFINITY_CFG['default_module'];
			$action = '';
		} else {
			if (strpos($qs, '/') === false) {
				$module = $qs;
				$action = '';
			} else {
				$pieces = explode('/', $qs);
				$module = $pieces[0];
				$action = $pieces[1];
				if (isset($_INFINITY_ROUTE[$module][$action])) {
					foreach ($_INFINITY_ROUTE[$module][$action] as $index => $variable)
						if (isset($pieces[($index + 2)]))
							$_REQUEST[$variable] = $pieces[($index + 2)];
						else
							$_REQUEST[$variable] = null;
				}
			}
		}
	} else {
		//defines the module
		if (isset($_REQUEST['m']))
			$module = strtolower($_REQUEST['m']);
		else if (isset($_REQUEST['mod']))
			$module = strtolower($_REQUEST['mod']);
		else if (isset($_REQUEST['module']))
			$module = strtolower($_REQUEST['module']);
		else
			$module = $_INFINITY_CFG['default_module'];
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
		$controller = AUTOLOAD::load_controller($module, $log);
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
			//creates an instance of cache class
			$cache = new CACHE($module, $action);
			//checks if action exists
			if ((method_exists($controller, $action)) && (is_callable(array($controller, $action)))) {
				//calls pre-action function
				$controller->pre_action();
				//checks if action is cacheable
				$cacheable = $controller->cacheable($action);
				if ($cacheable === false)
					//calls the controller's action
					$controller->$action();
				else {
					//checks if cache has cached version of action
					if ($cache->has())
						//dispatches cached version
						$cache->get();
					else {
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
					//checks if cache has cached version of action
					if ($cache->has()) {
						//dispatches cached version
						$cache->get();
						//prevents default page to be shown
						exit;
					} else {
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

?>
