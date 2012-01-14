<?php
/**
* Worker control
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

	mb_internal_encoding('UTF-8');

	if ($argc < 2)
		exit;

	if (!preg_match('/[a-zA-Z0-9-_]+/', $argv[1]))
		exit;

	if ($argc == 2) {
		$argc++;
		$argv[2] = 'start';
	}

	require_once __DIR__.'/core/autoload.class.php';

	$path = new PATH;
	if ((file_exists($path->absolute('worker').$argv[1].'.worker.php')) && (is_file($path->absolute('worker').$argv[1].'.worker.php'))) {
		switch ($argv[2]) {
			case 'start':
				echo 'worker start'."\n";
				AUTOLOAD::load_plugin('lock');
				$lock = new LOCK($argv[1]);
				if (!$lock->lock())
					exit;
				$pid = getmypid();
				echo 'worker pid: '.$pid."\n";
				@file_put_contents(sys_get_temp_dir().'/'.$argv[1].'.pid', $pid);
				require_once $path->absolute('worker').$argv[1].'.worker.php';
				break;
			case 'stop':
				echo 'worker stop'."\n";
				AUTOLOAD::load_plugin('lock');
				$lock = new LOCK($argv[1]);
				if ((!$lock->lock()) && (file_exists(sys_get_temp_dir().'/'.$argv[1].'.pid'))) {
					$pid = @file_get_contents(sys_get_temp_dir().'/'.$argv[1].'.pid');
					echo 'worker pid: '.$pid."\n";
					exec('kill -9 '.$pid);
				} else
					echo 'worker not running'."\n";
				break;
		}
	}
