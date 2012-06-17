<?php
/**
* Basic Cryptography abstraction
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

class SECURE {
	private $seed = '';

	public function __construct() {
		$config = CONFIGURATION::singleton();
		if (isset($config->framework['secure']['seed']))
			$this->seed = $config->framework['secure']['seed'];
	}

	public function md5($data) {
		return hash('md5', $this->seed.$data);
	}

	public function sha256($data) {
		return hash('sha256', $this->seed.$data);
	}

	public function sha512($data) {
		return hash('sha512', $this->seed.$data);
	}

	public function encrypt_3des($key, $data, $hash = 'sha256') {
		return base64_encode($this->encrypt($key, $data, MCRYPT_3DES, $hash));
	}

	public function decrypt_3des($key, $data, $hash = 'sha256') {
		return $this->decrypt($key, base64_decode($data), MCRYPT_3DES, $hash);
	}

	public function encrypt_blowfish($key, $data, $hash = 'sha256') {
		return base64_encode($this->encrypt($key, $data, MCRYPT_BLOWFISH, $hash));
	}

	public function decrypt_blowfish($key, $data, $hash = 'sha256') {
		return $this->decrypt($key, base64_decode($data), MCRYPT_BLOWFISH, $hash);
	}

	public function encrypt_rijndael($key, $data, $hash = 'sha256') {
		return base64_encode($this->encrypt($key, $data, MCRYPT_RIJNDAEL_256, $hash));
	}

	public function decrypt_rijndael($key, $data, $hash = 'sha256') {
		return $this->decrypt($key, base64_decode($data), MCRYPT_RIJNDAEL_256, $hash);
	}

	private function encrypt($key, $data, $cipher = MCRYPT_3DES, $hash = 'sha256') {
		$td = mcrypt_module_open($cipher, '', MCRYPT_MODE_ECB, '');
		if ($td === false)
			return false;
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
		if ($iv === false)
			return false;
		$ks = mcrypt_enc_get_key_size($td);
		switch ($hash) {
			case 'md5':
				$key = substr($this->md5($key), 0, $ks);
			case 'sha256':
				$key = substr($this->sha256($key), 0, $ks);
			case 'sha512':
				$key = substr($this->sha512($key), 0, $ks);
			case 'none':
			default:
				$key = substr($key, 0, $ks);
		}
		mcrypt_generic_init($td, $key, $iv);
		$ret = mcrypt_generic($td, $data);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $iv.$ret;
	}

	private function decrypt($key, $data, $cipher = MCRYPT_3DES, $hash = 'sha256') {
		$td = mcrypt_module_open($cipher, '', MCRYPT_MODE_ECB, '');
		if ($td === false)
			return false;
		$size = mcrypt_enc_get_iv_size($td);
		$iv = substr($data, 0, $size);
		$data = substr($data, $size);
		$ks = mcrypt_enc_get_key_size($td);
		switch ($hash) {
			case 'md5':
				$key = substr($this->md5($key), 0, $ks);
			case 'sha256':
				$key = substr($this->sha256($key), 0, $ks);
			case 'sha512':
				$key = substr($this->sha512($key), 0, $ks);
			case 'none':
			default:
				$key = substr($key, 0, $ks);
		}
		mcrypt_generic_init($td, $key, $iv);
		$ret = mdecrypt_generic($td, $data);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $ret;
	}

}
