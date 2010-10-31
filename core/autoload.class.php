<?php

	class AUTOLOAD {

		public static function loadPlugin($file) {
			if (file_exists($file)) {
				require_once $file;
				return true;
			} else
				return false;
		}

		public static function loadController($module, $session, $email, $msg, $path) {
			$file = __DIR__.'/../app/'.strtolower($module).'.controller.php';
			if (file_exists($file)) {
				require_once $file;
				$name = strtoupper($module);
				$module = $name.'_CONTROLLER';
				return new $module($name, $session, $email, $msg, $path);
			} else {
				require_once __DIR__.'/controller.class.php';
				return new CONTROLLER(strtoupper($module), $session, $email, $msg, $path);
			}
		}

		public static function loadAuxController() {
			$file = __DIR__.'/../app/aux.controller.php';
			if (file_exists($file)) {
				require_once $file;
				return new AuxController();
			} else
				return null;
		}

		public static function loadView($module, $template, $cache, $msg) {
			$file = __DIR__.'/../app/'.strtolower($module).'.view.php';
			if (file_exists($file)) {
				require_once $file;
				$module = strtoupper($module).'_VIEW';
				return new $module($template, $cache, $msg);
			} else
				return null;
		}

		public static function loadAuxView() {
			$file = __DIR__.'/../app/aux.view.php';
			if (file_exists($file)) {
				require_once $file;
				return new AuxView();
			} else
				return null;
		}

		public static function loadModel($module) {
			$file = __DIR__.'/../app/'.strtolower($module).'.model.php';
			if (file_exists($file)) {
				require_once $file;
				$module = strtoupper($module).'_MODEL';
				return new $module();
			} else
				return null;
		}

		public static function loadAuxModel() {
			$file = __DIR__.'/../app/aux.model.php';
			if (file_exists($file)) {
				require_once $file;
				return new AuxModel();
			} else
				return null;
		}

	}

?>
