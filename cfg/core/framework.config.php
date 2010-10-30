<?php

	$_INFINITY_CFG = array(
		'default_page' => 'default', //default page displayed when no valid controller is found
		'default_module' => 'main', //module that will be used when no module is provided
		'routes' => array( //defines routes for controller functions (instead of calling KEY, will call VALUE)
		),
		'domain' => 'localhost',
		'base_path' => '/', //defines the web path to scripts
		'email' => array( //defines email system configuration
			'host' => 'localhost',
			'port' => 25,
			'accs' => array(
				'system' => array(
					'name' => '',
					'user' => '',
					'pass' => '',
					'reply' => array(
						'name' => '',
						'mail' => ''
					)
				)
			)
		)
	);

?>
