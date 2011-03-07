<?php

	$_INFINITY_CFG = array(
		'default_module' => 'main', //module that will be used when no module is provided
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
