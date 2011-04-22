<?php

	class DISPATCH {

		public static function json_response(array $response) {
			echo json_encode($response)."\n";
		}

		public static function plain_response($response) {
			echo $response."\n";
		}

	}

?>
