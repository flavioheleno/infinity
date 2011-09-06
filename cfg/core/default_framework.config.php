<?php

	$_infinity = array(
		'benchmark' => true, //defines if benchmark should be calculated
		'debug' => true, //defines debug mode (display errors or not)
		'default_module' => 'main', //module that will be used when no module is provided
		'domain' => 'localhost', //defines the domain name
		'base_path' => '/', //defines the web path to scripts
		'route' => false, //defines the routing status
		'session' => array( //defines session configuration
			'name' => 'infinity', //defines the session name
			'subdomain' => true, //defines if cookies must be valid for subdomains
			'localhost' => true, //defines if the app is running in localhost
			'idletime' => 1800 //defines max idle time of session until logout
		),
		'secure' => array( //defines secure system configuration
			'seed' => '' //defines the secure class seed for encrypt/decrypt and hash operations
		),
		'cache' => array( //defines system's cache configuration
			'enabled' => true, //defines cache work state
		),
	);
