<?php
/**
* Framework configuration
*
* @version 0.1
* @author Flávio Heleno <flaviohbatista@gmail.com>
* @link http://code.google.com/p/infinity-framework
* @copyright Copyright (c) 2010/2011, Flávio Heleno
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*/

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
