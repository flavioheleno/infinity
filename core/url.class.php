<?php

	require_once __DIR__.'/cfg/core/framework.config.php';

	class URL {

		public function create(array $param) {
			global $_INFINITY_CFG;
			parse_str($_SERVER['QUERY_STRING'], $query);
			foreach ($param as $key => $value)
				$query[$key] = $value;
			return $_INFINITY_CFG['path'].'?'.http_build_query($query);
		}

	}

?>
