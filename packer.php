<?php
/**
* Framework packer
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

	define('PACK_APP', 'tar');
	define('PACK_PAR', '-pjc --exclude-vcs --exclude-backups -f');
	define('PACK_FNA', 'infinity_current');
	define('PACK_EXT', '.tar.bz2');
	define('PACK_CNT', 'cfg/ core/ css/ img/ js/ plugin/ index.php readme.txt setup.php worker.php');

	function display_help() {
		echo 'Usage:'."\n";
		echo "\t".'php -f '.__FILE__.' [filename]'."\n";
	}

	function info($text) {
		echo ' * '.$text."\n";
	}

	function abort($text) {
		echo ' ! '.$text."\n";
		exit;
	}

	echo 'INFINITY FRAMEWORK - Packer'."\n\n";

	$fn = PACK_FNA.PACK_EXT;
	if ($argc == 2) {
		if (in_array($argv[1], array('help', '?'))) {
			display_help();
			exit;
		} else
			$fn = $argv[1].PACK_EXT;
	}

	$ret = exec(PACK_APP.' '.PACK_PAR.' '.$fn.' '.PACK_CNT);
	if ($ret == '')
		info('Package created ('.$fn.')');
	else
		abort($ret);
