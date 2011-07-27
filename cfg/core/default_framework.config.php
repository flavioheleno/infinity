<?php

	$_infinity = array(
		'benchmark' => true,
		'default_module' => 'main', //module that will be used when no module is provided
		'domain' => 'localhost', //defines the domain name
		'subdomain' => true, //defines if cookies must be valid for subdomains
		'idletime' => 1800, //defines max idle time of session until logout
		'base_path' => '/', //defines the web path to scripts
		'route' => false, //defines the routing status
		'secure' => array( //defines secure system configuration
			'seed' => '' //defines the secure class seed for encrypt/decrypt and hash operations
		),
		'cache' => array( //defines system's cache configuration
			'enabled' => true, //defines cache work state
		),
	);

?>
