<?php

	require_once __DIR__.'/../cfg/core/framework.config.php';

	class URL {

		public function create(array $param, $html = true) {
			global $_INFINITY_CFG;
			parse_str($_SERVER['QUERY_STRING'], $query);
			foreach ($param as $key => $value)
				$query[$key] = $value;
			if ($html)
				return $_INFINITY_CFG['base_path'].'?'.http_build_query($query, '', '&amp;');
			else
				return $_INFINITY_CFG['base_path'].'?'.http_build_query($query);
		}

	}

?>
