<?php

	if (!defined('__DIR__'))
		define('__DIR__', dirname(__FILE__));

	require_once __DIR__.'/core/autoload.class.php';
	require_once __DIR__.'/cfg/core/framework.config.php';

	global $_INFINITY_CFG;

	if ($_INFINITY_CFG['benchmark'])
		register_shutdown_function(function($start) {
			echo '<!-- benchmark '.round(((microtime(true) - $start) * 1000), 2).'ms -->';
		}, microtime(true));

	//creates log object
	$log = LOG::singleton('infinity.log');

	//defines the module
	if (isset($_REQUEST['m']))
		$module = strtolower($_REQUEST['m']);
	else if (isset($_REQUEST['mod']))
		$module = strtolower($_REQUEST['mod']);
	else if (isset($_REQUEST['module']))
		$module = strtolower($_REQUEST['module']);
	else
		$module = $_INFINITY_CFG['default_module'];
	$log->add('Module parameter: '.$module);


	//checks if module name is well formed
	if (preg_match('/^[a-z_][a-z0-9_-]+$/i', $module))
		//grabs a new instance of module
		$controller = AUTOLOAD::load_controller($module, $log);
	else
		$controller = null;

	//if the module exists
	if (!is_null($controller)) {
		//defines the action
		if (isset($_REQUEST['a']))
			$action = strtolower($_REQUEST['a']);
		else if (isset($_REQUEST['act']))
			$action = strtolower($_REQUEST['act']);
		else if (isset($_REQUEST['action']))
			$action = strtolower($_REQUEST['action']);
		else
			$action = $controller->default_action;
		$log->add('Action parameter: '.$action);

		//checks if action name is well formed
		if ((preg_match('/^[a-z_][a-z0-9_-]+$/i', $action)) && (substr($action, 0, 2) != '__')) {
			//checks if there is an alias for the given module->action and updates it
			$controller->check_alias($action);
			//creates an instance of cache class
			$cache = new CACHE($module, $action);
			//checks if action exists
			if (method_exists($controller, $action)) {
				//checks if action is cacheable
				$cacheable = $controller->cacheable($action);
				if ($cacheable === false)
					//calls the controller's action
					$controller->$action();
				else {
					//checks if cache has cached version of action
					if ($cache->has())
						//dispatches cached version
						DISPATCH::plain_response($cache->get());
					else {
						//starts output buffering
						if (!ob_start('ob_gzhandler'))
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
				//prevents default page to be shown
				exit;
			} else {
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
						DISPATCH::plain_response($cache->get());
						//prevents default page to be shown
						exit;
					} else {
						//starts output buffering
						if (!ob_start('ob_gzhandler'))
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
