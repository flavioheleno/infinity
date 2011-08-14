<?php

	class MAIN_VIEW extends VIEW {

		public function index() {
			$this->response = 'hello world!';
			$this->dispatch();
		}

	}
