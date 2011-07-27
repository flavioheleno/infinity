<?php

	class URL {

		public static function create($param, $html = true) {
			$config = CONFIGURATION::singleton();
			if ($config->framework['route'])
				return $config->framework['base_path'].implode('/', $param);
			else {
				parse_str($_SERVER['QUERY_STRING'], $query);
				foreach ($param as $key => $value)
					$query[$key] = $value;
				if ($html)
					return $config->framework['base_path'].'?'.http_build_query($query, '', '&amp;');
				else
					return $config->framework['base_path'].'?'.http_build_query($query);
			}
		}

	}

?>
