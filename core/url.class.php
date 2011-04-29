<?php

	AUTOLOAD::require_core_config('framework');

	class URL {

		public static function create($param, $html = true) {
			global $_INFINITY_CFG;
			if ($_INFINITY_CFG['route'])
				return $_INFINITY_CFG['base_path'].implode('/', $param);
			else {
				parse_str($_SERVER['QUERY_STRING'], $query);
				foreach ($param as $key => $value)
					$query[$key] = $value;
				if ($html)
					return $_INFINITY_CFG['base_path'].'?'.http_build_query($query, '', '&amp;');
				else
					return $_INFINITY_CFG['base_path'].'?'.http_build_query($query);
			}
		}

	}

?>
