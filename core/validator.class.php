<?php

	class VALIDATOR {

		public static function cleanup($value) {
			$a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
			$b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
			$value = utf8_decode($value);
			$value = strtr($value, utf8_decode($a), $b);
			return utf8_encode($value);
		}

		public static function check($value, $rules) {
			$valid = true;
			$value = self::cleanup($value);
			foreach ($rules as $k => $v) {
				if ($v)
					switch ($k) {
						case 'required':
							$valid &= ($value != '');
							break;
						case 'alphanumeric':
							$valid &= preg_match('/^[0-9a-z _-]+$/i', $value);
							break;
						case 'number':
							$valid &= preg_match('/^[0-9]+$/', $value);
							break;
						case 'decimal':
							$valid &= preg_match('/^[0-9]+,[0-9]+$/', $value);
							break;
						case 'string':
							$valid &= preg_match('/^[a-z _-]+$/i', $value);
							break;
						case 'email':
							
							break;
						case 'url':
							
							break;
						case 'date':
							$valid &= preg_match('/^(0[1-9]|1[0-9]|2[0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[1-9][0-9]{3}$/', $value);
							break;
						case 'min':
							$valid &= ($value >= intval($v));
							break;
						case 'max':
							$valid &= ($value <= intval($v));
							break;
						case 'range':
							if ((is_array($v)) && (count($v) == 2))
								$valid &= (($value >= intval($v[0])) && ($value <= intval($v[1])));
							else
								$valid = false;
							break;
						case 'minlength':
							$valid &= (strlen($value) >= intval($v));
							break;
						case 'maxlength':
							$valid &= (strlen($value) <= intval($v));
							break;
						case 'rangelength':
							if ((is_array($v)) && (count($v) == 2))
								$valid &= ((strlen($value) >= intval($v[0])) && (strlen($value) <= intval($v[1])));
							else
								$valid = false;
							break;
						default:
							die('invalid validator rule: '.$k);
					}
			}
			return $valid;
		}

	}

?>
