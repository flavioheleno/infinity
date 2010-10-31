<?php

	require_once __DIR__.'/sql.class.php';

	class RECORD {
		//holds table name
		private $table = '';
		//holds instance of class sql
		private $sql = null;
		//holds the values
		private $values = array();

		public function __construct($table = '', array $cfg) {
			$this->table = $table;
			$this->sql = new SQL($cfg);
		}

		public function __get($index) {
			if (isset($this->values[$index]))
				return $this->values[$index];
			else
				return null;
		}

		public function __set($index, $value) {
			$this->values[$index] = $value;
		}

		public function __unset($index) {
			unset($this->values[$index]);
		}

		public function __isset($index) {
			return isset($this->values[$index]);
		}

		public function clean() {
			$this->values = array();
		}

		public function create() {
			return $this->sql->insert($this->table, $this->value);
		}

		public function read() {
			$w = array();
			foreach ($this->value as $item) {
				$w[] = $item;
				$w[] = 'AND';
			}
			$q = $this->sql->select($this->table, null, null, $w);
			$r = array();
			while ($tmp = $this->sql->next($q))
				$r[] = $tmp;
			return $r;
		}

		public function retrieve() {
			$w = array();
			foreach ($this->value as $item) {
				$w[] = $item;
				$w[] = 'OR';
			}
			$q = $this->sql->select($this->table, null, null, $w);
			$r = array();
			while ($tmp = $this->sql->next($q))
				$r[] = $tmp;
			return $r;
		}

		public function update() {
			
		}

		public function destroy() {
			return $this->sql->delete($this->table, $this->value);
		}

	}

?>
