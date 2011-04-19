<?php

	require_once __DIR__.'/../cfg/core/framework.config.php';

	class SECURE {
		private $seed = '';

		public function __construct() {
			global $_INFINITY_CFG;
			if (isset($_INFINITY_CFG['secure_seed']))
				$this->seed = $_INFINITY_CFG['secure_seed'];
		}

		public function hash($data) {
			return hash('sha256', $this->seed.$data);
		}

		public function encrypt($key, $data) {
			$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
			if ($td === false)
				return false;
			$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
			if ($iv === false)
				return false;
			$ks = mcrypt_enc_get_key_size($td);
			$key = substr($this->hash($key), 0, $ks);
			mcrypt_generic_init($td, $key, $iv);
			$ret = mcrypt_generic($td, $data);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			return base64_encode($iv.$ret);
		}

		public function decrypt($key, $data) {
			$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
			if ($td === false)
				return false;
			$data = base64_decode($data);
			$size = mcrypt_enc_get_iv_size($td);
			$iv = substr($data, 0, $size);
			$data = substr($data, $size);
			$ks = mcrypt_enc_get_key_size($td);
			$key = substr($this->hash($key), 0, $ks);
			mcrypt_generic_init($td, $key, $iv);
			$ret = mdecrypt_generic($td, $data);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			return $ret;
		}

	}

	$s = new SECURE;
	$e = $s->encrypt('minha senha legal', 'meu texto é muuuito legal huaehauehuaehuaehuaehuae :D');
	echo '\''.$e.'\''."\n";
	echo '\''.$s->decrypt('minha senha legal', $e).'\''."\n";

?>
