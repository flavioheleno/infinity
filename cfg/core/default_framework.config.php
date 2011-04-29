<?php

	$_INFINITY_CFG = array(
		'benchmark' => true,
		'default_module' => 'main', //module that will be used when no module is provided
		'domain' => 'localhost',
		'base_path' => '/', //defines the web path to scripts
		'route' => false, //defines the routing status
		'secure' => array( //defines secure system configuration
			'seed' => '' //defines the secure class seed for encrypt/decrypt and hash operations
		),
		'cache' => array( //defines system's cache configuration
			'enabled' => true, //defines cache work state
		),
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
