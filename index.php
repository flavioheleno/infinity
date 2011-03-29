<?php

	require_once __DIR__.'/core/autoload.class.php';
	require_once __DIR__.'/core/log.class.php';
	require_once __DIR__.'/core/msg.class.php';
	require_once __DIR__.'/cfg/core/framework.config.php';

	global $_INFINITY_CFG;

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
			$action = 'index';
		$log->add('Action parameter: '.$action);

		//checks if action name is well formed
		if ((preg_match('/^[a-z_][a-z0-9_-]+$/i', $action)) && (substr($action, 0, 2) != '__')) {
			//checks if there is a route for the given module->action and updates it
			$controller->check_route($action);
			//creates enviroment variable to hold request data, server data and user data
			$env = array(
				'request' => $_REQUEST,
				'server' => $_SERVER,
				'user' => array()
			);
			//checks if action exists
			if (method_exists($controller, $action)) {
				//calls the controller's action
				$controller->$action($env);
				//prevents default page to be shown
				exit;
			} else {
				//tries to call the controller's action
				if ($controller->$action($env))
					//prevents default page to be shown
					exit;
			}
		}
	}
	$log->add('Can\'t find '.$module.'->'.$action.', showing default page');
	header('Status: 404 Not Found');
	echo '404 - Not Found';
?>
