<?php
	require_once __DIR__.'/db.class.php';

	class SQL {
		private $db = null;
		private $status = false;
		private $valid = false;
		private $last_query = '';
		
		private $glue = array('AND', 'OR');
		private $oper = array('=', '<', '>', '<>', '<=', '=<', '>=', '=>');
		private $func = array('CURDATE', 'CURRENT_DATE', 'CURTIME', 'CURRENT_TIME', 'NOW', 'CURRENT_TIMESTAMP', 'DAY', 'MONTH', 'YEAR', 'HOUR', 'MINUTE', 'SECOND', 'COUNT', 'MIN', 'MAX', 'TIMESTAMPDIFF', 'UNIX_TIMESTAMP', 'SHA1', 'CONCAT', 'MD5', 'CAST', 'DATE_ADD', 'DATE_SUB');

		function __construct(array $cfg) {
			$this->db = new DB($cfg);
			$this->status = $this->db->connect();
		}

		function __destruct() {
			if (!is_null($this->db))
				$this->db->disconnect();
		}

		private function field($data) {
			$ret = array();
			if ((is_null($data)) || ($data == '*'))
				$ret[] = '*';
			else if (is_array($data)) {
				if (count($data))
					foreach ($data as $item)
						$ret[] = $this->field($item);
				else
					$ret[] = '*';
			} else {
				if (in_array(substr(strtoupper($data), 0, strpos($data, '(')), $this->func))
					$ret[] = $data;
				else if (strpos($data, '.')) {
					$tmp = explode('.', $data);
					if (strpos($tmp[1], ' ')) {
						$tmp2 = explode(' ', $tmp[1]);
						$ret[] = $this->field($tmp[0]).'.'.$this->field($tmp2[0]).substr($tmp[1], strpos($tmp[1], ' '));
					} else
						$ret[] = $this->field($tmp[0]).'.'.$this->field($tmp[1]);
				} else if (strpos($data, ' ')) {
					$tmp = explode(' ', $data);
					$ret[] = $this->field($tmp[0]).' '.$tmp[1].' '.$this->protect($tmp[2]);
				} else
					$ret[] = '`'.$data.'`';
			}
			return implode(', ', $ret);
		}

		public function protect($data) {
			if (is_null($data))
				return 'NULL';
			else {
				$data = filter_var($data, FILTER_UNSAFE_RAW, array('flags' => FILTER_FLAG_STRIP_LOW));
				if (is_numeric($data)) {
					if (is_float($data))
						return floatval($data);
					else
						return intval($data);
				} else {
					if ((substr($data, 0, 1) == '`') && (substr($data, -1) == '`'))
						return $this->db->quote($data);
					else if (in_array(substr(strtoupper($data), 0, strpos($data, '(')), $this->func))
						return substr(strtoupper($data), 0, strpos($data, '(')).$this->db->quote(substr($data, strpos($data, '(')));
					else
						return '\''.$this->db->quote(utf8_encode(trim($data))).'\'';
				}
			}
		}

		private function where($condition = array()) {
			$ret = '';
			$oper = false;
			foreach($condition as $item) {
				if (is_array($item))
					$ret .= $this->where($item);
				else {
					if (in_array(strtoupper($item), $this->glue, true))
						$ret .= ' '.strtoupper($item).' ';
					else if (in_array($item, $this->oper, true)) {
						$oper = true;
						$ret .= ' '.$item.' ';
					} else if ($oper) {
						$oper = false;
						$ret .= $this->protect($item);
					} else
						$ret .= $this->field($item);
				}
			}
			return '('.$ret.')';
		}

		public function status() {
			return $this->status;
		}

		public function transactionOpen() {
			if (!is_null($this->db))
				$this->db->blockBegin();
		}

		public function transactionClose() {
			if (!is_null($this->db))
				$this->db->blockEnd();
		}

		public function transactionCancel() {
			if (!is_null($this->db))
				$this->db->blockCancel();
		}

		public function lastQuery() {
			return $this->last_query;
		}

		public function lastError() {
			if (!is_null($this->db))
				return $this->db->lastError();
			else
				return false;
		}

		public function lastID() {
			if (!is_null($this->db))
				return $this->db->lastInsertID();
			else
				return false;
		}

		public function count($resource = false) {
			if (!is_null($this->db)) {
				if (!$this->valid) {
					if ($resource)
						return $this->db->numRows($resource);
					else
						return false;
				} else
					return $this->db->affectedRows();
			} else
				return false;
		}

		public function next($resource = false) {
			if ($resource)
				return $this->db->fetchAssoc($resource);
			else
				return false;
		}

		public function seek($resource = false, $count) {
			if ($resource)
				return $this->db->seek($resource, $count);
			else
				return false;
		}

		public function free($resource = false) {
			if ($resource)
				$this->db->free($resource);
		}

		public function raw($command = null) {
			$this->valid = false;
			if (!is_null($this->db)) {
				if (!is_null($command)) {
					$this->last_query = $command;
					$this->db->query($command);
				} else
					return false;
			} else
				return false;
		}

		public function select($table = '', $fields = null, $join = null, $condition = null, $group = null, $having = null, $order = null, $limit = null, $raw = null) {
			$this->valid = false;
			if (!is_null($this->db)) {
				$table = trim($table);
				if ($table != '') {
					$sql_fields = $this->field($fields);

					if (!is_null($join)) {
						if ((is_array($join)) && (count($join)))
							$sql_join = implode(' ', $join);
						else if (is_string($join))
							$sql_join = $join;
						else
							$sql_join = false;
					} else
						$sql_join = false;

					if (!is_null($condition)) {
						if ((is_array($condition)) && (count($condition)))
							$sql_condition = $this->where($condition);
						else if (is_string($condition))
							$sql_condition = $condition;
						else
							$sql_condition = false;
					} else
						$sql_condition = false;

					if (!is_null($group)) {
						if ((is_array($group)) && (count($group)))
							$sql_group = '`'.implode('`, `', $group).'`';
						else if (is_string($group))
							$sql_group = '`'.$group.'`';
						else
							$sql_group = false;
					} else
						$sql_group = false;

					if (!is_null($having)) {
						if ((is_array($having)) && (count($having)))
							$sql_having = $this->where($having);
						else if (is_string($having))
							$sql_having = $having;
						else
							$sql_having = false;
					} else
						$sql_having = false;

					if (!is_null($order)) {
						if ((is_array($order)) && (count($order))) {
							$tmp = array();
							foreach ($order as $key => $value)
								$tmp[] = '`'.$key.'` '.strtoupper($value);
							$sql_order = implode(', ', $tmp);
						} else if (is_string($order))
							$sql_order = $order;
						else
							$sql_order = false;
					} else
						$sql_order = false;

					if (!is_null($limit)) {
						if ((is_array($limit)) && (count($limit)))
							$sql_limit = implode(', ', $limit);
						else if ((is_string($limit)) || (is_int($limit)))
							$sql_limit = intval($limit);
						else
							$sql_limit = false;
					} else
						$sql_limit = false;

					$sql = 'SELECT '.$sql_fields.' FROM `'.$table.'`';
					if ($sql_join)
						$sql .= ' '.$sql_join;
					if ($sql_condition)
						$sql .= ' WHERE '.$sql_condition;
					if ($sql_group)
						$sql .= ' GROUP BY '.$sql_group;
					if ($sql_having)
						$sql .= ' HAVING '.$sql_having;
					if ($sql_order)
						$sql .= ' ORDER BY '.$sql_order;
					if ($sql_limit)
						$sql .= ' LIMIT '.$sql_limit;
					if (!is_null($raw))
						$sql .= ' '.$raw;
					$this->last_query = $sql;
					return $this->db->query($sql);
				} else
					return false;
			} else
				return false;
		}

		public function insert($table = '', $values = null, $duplicate = null, $raw = null) {
			$this->valid = false;
			if (!is_null($this->db)) {
				$table = trim($table);
				if ($table != '') {
					if ((!is_null($values)) && (is_array($values)) && (count($values))) {
						$f = array();
						$v = array();
						foreach ($values as $key => $value) {
							$f[] = $this->field($key);
							$v[] = $this->protect($value);
						}
						if (!is_null($duplicate)) {
							$tmp = array();
							if ((is_array($duplicate)) && (count($duplicate))) {
								foreach ($values as $key => $value)
									if (in_array($key, $duplicate))
										$tmp[] = $this->field($key).' = '.$this->protect($value);
								$sql_duplicate = implode(', ', $tmp);
							} else if (is_string($duplicate))
								$sql_duplicate = $this->field($duplicate).' = '.$this->protect($values[$duplicate]);
						} else
							$sql_duplicate = false;
						$sql = 'INSERT INTO `'.$table.'` ';
						$sql .= '('.implode(', ', $f).')';
						$sql .= ' VALUES ';
						$sql .= '('.implode(', ', $v).')';
						if ($sql_duplicate)
							$sql .= ' ON DUPLICATE KEY UPDATE '.$sql_duplicate;
						if (!is_null($raw))
							$sql .= ' '.$raw;
						$this->last_query = $sql;
						$this->valid = true;
						return $this->db->query($sql);
					} else
						return false;
				} else
					return false;
			} else
				return false;
		}

		public function delete($table = '', $condition = null, $limit = null, $raw = null) {
			$this->valid = false;
			if (!is_null($this->db)) {
				$table = trim($table);
				if ($table != '') {
					if (!is_null($condition)) {
						if ((is_array($condition)) && (count($condition)))
							$sql_condition = $this->where($condition);
						else if (is_string($condition))
							$sql_condition = $condition;
						else
							$sql_condition = false;
					} else
						$sql_condition = false;

					if (!is_null($limit)) {
						if ((is_string($limit)) || (is_int($limit)))
							$sql_limit = intval($limit);
						else
							$sql_limit = false;
					} else
						$sql_limit = false;

					$sql = 'DELETE FROM `'.$table.'`';
					if ($sql_condition)
						$sql .= ' WHERE '.$sql_condition;
					if ($sql_limit)
						$sql .= ' LIMIT '.$sql_limit;
					if (!is_null($raw))
						$sql .= ' '.$raw;
					$this->last_query = $sql;
					$this->valid = true;
					return $this->db->query($sql);
				} else
					return false;
			} else
				return false;
		}

		public function update($table = '', $fields = null, $condition = null, $limit = null, $raw = null) {
			$this->valid = false;
			if (!is_null($this->db)) {
				$table = trim($table);
				if ($table != '') {
					if ((!is_null($fields)) && (is_array($fields)) && (count($fields))) {
						$u = array();
						foreach ($fields as $key => $value)
							$u[] = $this->field($key).' = '.$this->protect($value);

						if (!is_null($condition)) {
							if ((is_array($condition)) && (count($condition)))
								$sql_condition = $this->where($condition);
							else if (is_string($condition))
								$sql_condition = $condition;
							else
								$sql_condition = false;
						} else
							$sql_condition = false;

						if (!is_null($limit)) {
							if ((is_string($limit)) || (is_int($limit)))
								$sql_limit = intval($limit);
							else
								$sql_limit = false;
						} else
							$sql_limit = false;

						$sql = 'UPDATE `'.$table.'`';
						$sql .= ' SET ';
						$sql .= implode(', ', $u);
						if ($sql_condition)
							$sql .= ' WHERE '.$sql_condition;
						if ($sql_limit)
							$sql .= ' LIMIT '.$sql_limit;
						if (!is_null($raw))
							$sql .= ' '.$raw;
						$this->last_query = $sql;
						$this->valid = true;
						return $this->db->query($sql);
					} else
						return false;
				} else
					return false;
			} else
				return false;
		}
	}
?>
