<?php

	class AUTOLOAD {

		public static function load_plugin($file) {
			if (file_exists($file)) {
				require_once $file;
				return true;
			} else
				return false;
		}

		public static function load_controller($name, &$log, $domain, $path, $email) {
			$file = __DIR__.'/../app/'.strtolower($name).'.controller.php';
			if (file_exists($file)) {
				require_once $file;
				$name = strtoupper($name);
				$module = $name.'_CONTROLLER';
				return new $module($name, $log, $domain, $path, $email);
			} else {
				require_once __DIR__.'/controller.class.php';
				return new CONTROLLER(strtoupper($name), $log, $domain, $path, $email);
			}
		}

		public static function load_aux_controller() {
			$file = __DIR__.'/../app/aux.controller.php';
			if (file_exists($file)) {
				require_once $file;
				return new AuxController;
			} else
				return null;
		}

		public static function load_view($name, &$log) {
			$file = __DIR__.'/../app/'.strtolower($name).'.view.php';
			if (file_exists($file)) {
				require_once $file;
				$name = strtoupper($name);
				$module = $name.'_VIEW';
				return new $module($name, $log);
			} else
				return null;
		}

		public static function load_aux_view() {
			$file = __DIR__.'/../app/aux.view.php';
			if (file_exists($file)) {
				require_once $file;
				return new AuxView;
			} else
				return null;
		}

		public static function load_model($name, &$log) {
			$file = __DIR__.'/../app/'.strtolower($name).'.model.php';
			if (file_exists($file)) {
				require_once $file;
				$name = strtoupper($name);
				$module = $name.'_MODEL';
				return new $module($name, $log);
			} else
				return null;
		}

		public static function load_aux_model() {
			$file = __DIR__.'/../app/aux.model.php';
			if (file_exists($file)) {
				require_once $file;
				return new AuxModel;
			} else
				return null;
		}

	}

?>
