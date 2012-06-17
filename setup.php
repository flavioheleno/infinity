<?php
/**
* Framework basic setup
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

define('CURRENT_URL', 'http://infinity-framework.googlecode.com/files/infinity_current.tar.bz2');
define('CORE_PATH', '/tmp/infinity_current/');
define('UNPACK_CMD', 'tar -jxf');

function display_help() {
	echo 'Usage:'."\n";
	echo "\t".'php -f '.__FILE__.' [options] folder'."\n\n";
	echo 'Options:'."\n";
	echo "\t".'skip-cache'."\t".'Skip cache folder creation (used in filecache)'."\n";
	echo "\t".'skip-log'."\t".'Skip log folder creation'."\n";
}

function info($text) {
	echo ' * '.$text."\n";
}

function abort($text) {
	echo ' ! '.$text."\n";
	exit;
}

function core_check() {
	info('Checking if core files are available');
	if ((file_exists(CORE_PATH)) && (is_dir(CORE_PATH))) {
		info('Core files found');
		return true;
	} else {
		info('Core files not found');
		return false;
	}
}

function core_download() {
	info('Downloading current core files');
	$src = @file_get_contents(CURRENT_URL);
	if ($src === false)
		abort('Download failed');
	$fn = tempnam('/tmp', 'infinity');
	if (!@file_put_contents($fn, $src))
		abort('Failed to write download data to disk');
	if ((!file_exists(CORE_PATH)) || (!is_dir(CORE_PATH)))
		if (!@mkdir(CORE_PATH))
			abort('Failed to create temporary dir to extract core files');
	$ret = exec(UNPACK_CMD.' '.$fn.' -C '.CORE_PATH);
	if ($ret == '') {
		@unlink($fn);
		info('Download ok');
		return true;
	} else
		abort($ret);
}

function copy_files($base, $path, $folder, $extension) {
	if (is_array($extension))
		$extension = implode('|', $extension);
	$regex = '/('.str_replace('.', '\.', $extension).')$/i';
	if (substr($path, -1) != '/')
		$path .= '/';
	$list = scandir($base.$path);
	foreach ($list as $file)
		if (preg_match($regex, $file)) {
			if (!@copy($base.$path.$file, $folder.$path.$file))
				abort('Failed to copy file ('.$file.')');
		}
}

function setup($base, $folder, array $options) {
	if (substr($base, -1) != '/')
		$base .= '/';
	if (substr($folder, -1) != '/')
		$folder .= '/';
	info('Setup folder: '.$folder);
	info('Options: '.count($options));
	if ((file_exists($folder)) && (is_dir($folder)))
		abort('Folder ('.$folder.') already exists');
	if (!@mkdir($folder))
		abort('Failed to create folder ('.$folder.')');
	info ('Creating directory structure');
	$ds = array(
		'app',
		'cache',
		'cfg' => array(
			'app',
			'core',
			'form'
		),
		'core',
		'css',
		'img',
		'js',
		'log',
		'mail',
		'plugin',
		'tpl' => array(
			'cache'
		),
		'worker'
	);
	$dp = array(
		'cache',
		'log',
		'tpl/cache'
	);
	if (isset($options['skip-cache'])) {
		info('Skiping cache folder creation');
		$k = array_search('cache', $ds);
		if ($k !== false)
			unset($ds[$k]);
		$k = array_search('cache', $dp);
		if ($k !== false)
			unset($dp[$k]);
	}
	if (isset($options['skip-log'])) {
		info('Skiping log folder creation');
		$k = array_search('log', $ds);
		if ($k !== false)
			unset($ds[$k]);
		$k = array_search('log', $dp);
		if ($k !== false)
			unset($dp[$k]);
	}
	foreach ($ds as $parent => $sub)
		if (is_array($sub)) {
			if (!@mkdir($folder.$parent))
				abort('Failed to create directory: '.$folder.$parent);
			foreach ($sub as $dir)
				if (!@mkdir($folder.$parent.'/'.$dir))
					abort('Failed to create directory: '.$folder.$parent.'/'.$dir);
		} else {
			if (!@mkdir($folder.$sub))
				abort('Failed to create directory: '.$folder.$sub);
		}
	foreach ($dp as $path)
		if (!@chmod($folder.$path, 0777))
			info('Can\'t chmod "'.$folder.$path.'"');
	info('Copying files');
	copy_files($base, 'cfg/core', $folder, '.php');
	copy_files($base, 'css', $folder, '.css');
	copy_files($base, 'core', $folder, '.php');
	copy_files($base, 'img', $folder, array('.ico', '.png', '.jpg', '.gif'));
	copy_files($base, 'js', $folder, '.js');
	if (!@copy($base.'index.php', $folder.'index.php'))
		abort('Failed to copy index file');
	if (!@copy($base.'worker.php', $folder.'worker.php'))
		abort('Failed to copy worker file');
	info('Don\'t forget to rename default_* config files into their real names');
	info('Finished');
}

echo 'INFINITY FRAMEWORK - Basic Setup'."\n\n";

if ($argc == 1) {
	display_help();
	exit;
}

if (!core_check())
	core_download();
$options = array();
if ((file_exists($argv[1])) && (is_dir($argv[1]))) {
	$folder = $argv[1];
} else {
	array_shift($argv);
	$folder = array_pop($argv);
	foreach ($argv as $option)
		$options[$option] = true;
}
setup(CORE_PATH, $folder, $options);
