<?php

	require_once __DIR__.'/core/log.class.php';
	require_once __DIR__.'/core/session.class.php';
	require_once __DIR__.'/core/email.class.php';
	require_once __DIR__.'/core/msg.class.php';
	require_once __DIR__.'/core/autoload.class.php';
	require_once __DIR__.'/cfg/core/framework.config.php';

	global $_INFINITY_CFG;

	//creates log object
	$log = new LOG('infinity.log');

	//creates session controller
	$session = new SESSION($_INFINITY_CFG['domain'], true);

	//creates email object
	$email = new EMAIL($_INFINITY_CFG['email']);

	//creates msg object
	$msg = new MSG();

	//defines the module
	if (isset($_REQUEST['m']))
		$module = strtolower($_REQUEST['m']);
	else if (isset($_REQUEST['mod']))
		$module = strtolower($_REQUEST['mod']);
	else if (isset($_REQUEST['module']))
		$module = strtolower($_REQUEST['module']);
	else
		$module = $_INFINITY_CFG['default_module'];

	//checks if module name is well formed
	if (preg_match('/^[a-z0-9_-]+$/i', $module))
		//grabs a new instance of module
		$controller = AUTOLOAD::loadController($module, $session, $email, $msg, $_INFINITY_CFG['base_path']);
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

		//checks if action name is well formed
		if ((preg_match('/^[a-z0-9_-]+$/i', $action)) && (substr($action, 0, 2) != '__')) {
			//checks if there is a route for the given module->action
			if (isset($_INFINITY_CFG['routes'][$module][$action]))
				$action = $_INFINITY_CFG['routes'][$module][$action];
			//checks if action exists
			if (method_exists($controller, $action)) {
				//calls the controller's action
				$controller->$action($_REQUEST);
				//prevents default page to be shown
				exit(0);
			} else {
				//tries to call the controller's action
				if ($controller->$action($_REQUEST))
					//prevents default page to be shown
					exit(0);
			}
		}
	}
	$log->add('Can\'t find '.$module.'->'.$action.', showing default page');
	$msg::page($_INFINITY_CFG['default_page']);
?>
