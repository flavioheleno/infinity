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

$path = PATH::singleton();
$worker = $path->absolute('worker').$argv[1].'.worker.php';
if ((file_exists($worker)) && (is_file($worker))) {
	AUTOLOAD::load_plugin('lock');
	$lock = new LOCK($argv[1]);
	switch ($argv[2]) {
		case 'start':
			if (!$lock->lock())
				exit;
			echo 'worker started'."\n";
			$pid = getmypid();
			echo 'worker pid: '.$pid."\n";
			$pidf = sys_get_temp_dir().'/'.$argv[1].'.pid';
			@file_put_contents($pidf, $pid);
			$worker_time = microtime(true);
			require_once $worker;
			$worker_time = (microtime(true) - $worker_time);
			echo 'worker finished'."\n";
			if ($worker_time < 0)
				echo 'worker took '.round(($worker_time * 1000), 2).' ms'."\n";
			else if ($worker_time < 60)
				echo 'worker took '.round($worker_time, 2).' s'."\n";
			else if ($worker_time < 3600)
				echo 'worker took '.round(($worker_time / 60), 2).' m'."\n";
			else
				echo 'worker took '.round(($worker_time / 3600), 2).' h'."\n";
			@unlink($pidf);
			$lock->unlock();
			break;
		case 'stop':
			$pidf = sys_get_temp_dir().'/'.$argv[1].'.pid';
			if ((!$lock->lock()) && (file_exists($pidf))) {
				echo 'worker stop'."\n";
				$pid = @file_get_contents($pidf);
				echo 'worker pid: '.$pid."\n";
				exec('kill -9 '.$pid);
			} else
				echo 'worker not running'."\n";
			break;
	}
}
