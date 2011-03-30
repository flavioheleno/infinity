<?php

	require_once __DIR__.'/db.class.php';

	class SQL {
		//instance of class DB
		private $db = null;
		//current status of DB connection
		private $status = false;
		//controls when the query executed was a select or not
		private $select = false;
		//holds the last performed query
		private $last_query = '';
		//holds table prefix
		private $prefix = '';
		
		//sql glue for statements
		private $glue = array('AND', 'OR');
		//sql logical operators
		private $oper = array('=', '<', '>', '<>', '<=', '=<', '>=', '=>');
		//sql functions
		private $func = array('CURDATE', 'CURRENT_DATE', 'CURTIME', 'CURRENT_TIME', 'NOW', 'CURRENT_TIMESTAMP', 'DAY', 'MONTH', 'YEAR', 'HOUR', 'MINUTE', 'SECOND', 'COUNT', 'MIN', 'MAX', 'TIMESTAMPDIFF', 'UNIX_TIMESTAMP', 'SHA1', 'CONCAT', 'MD5', 'CAST', 'DATE_ADD', 'DATE_SUB');

		function __construct(array $cfg) {
			if (isset($cfg['prefix']))
				$this->prefix = $cfg['prefix'];
			$this->db = new DB($cfg);
			$this->status = $this->db->connect();
		}

		function __destruct() {
			if (!is_null($this->db))
				$this->db->disconnect();
		}

		private function sql_field($data) {
			$ret = array();
			if ((is_null($data)) || ($data == '*'))
				$ret[] = '*';
			else if (is_array($data)) {
				if (count($data))
					foreach ($data as $item)
						$ret[] = $this->sql_field($item);
				else
					$ret[] = '*';
			} else {
				if (in_array(substr(strtoupper($data), 0, strpos($data, '(')), $this->func))
					$ret[] = $data;
				else if (strpos($data, '.')) {
					$tmp = explode('.', $data);
					if (strpos($tmp[1], ' ')) {
						$tmp2 = explode(' ', $tmp[1]);
						$ret[] = $this->sql_field($this->prefix.$tmp[0]).'.'.$this->sql_field($tmp2[0]).substr($tmp[1], strpos($tmp[1], ' '));
					} else
						$ret[] = $this->sql_field($this->prefix.$tmp[0]).'.'.$this->sql_field($tmp[1]);
				} else if (strpos($data, ' ')) {
					$tmp = explode(' ', $data);
					$ret[] = $this->sql_field($tmp[0]).' '.$tmp[1].' '.$this->protect($tmp[2]);
				} else
					$ret[] = '`'.$data.'`';
			}
			return implode(', ', $ret);
		}

		private function sql_join($join = null) {
			if (!is_null($join)) {
				if ((is_array($join)) && (count($join)))
					return implode(' ', $join);
				else if (is_string($join))
					return $join;
				else
					return false;
			} else
				return false;
		}

		private function sql_condition($condition = null) {
			if (!is_null($condition)) {
				if ((is_array($condition)) && (count($condition)))
					return $this->where($condition);
				else if (is_string($condition))
					return $condition;
				else
					return false;
			} else
				return false;
		}

		private function sql_group($group = null) {
			if (!is_null($group)) {
				if ((is_array($group)) && (count($group)))
					return '`'.implode('`, `', $group).'`';
				else if (is_string($group))
					return '`'.$group.'`';
				else
					return false;
			} else
				return false;
		}

		private function sql_having($having = null) {
			if (!is_null($having)) {
				if ((is_array($having)) && (count($having)))
					return $this->where($having);
				else if (is_string($having))
					return $having;
				else
					return false;
			} else
				return false;
		}

		private function sql_order($order = null) {
			if (!is_null($order)) {
				if ((is_array($order)) && (count($order))) {
					$tmp = array();
					foreach ($order as $key => $value)
						$tmp[] = '`'.$key.'` '.strtoupper($value);
					return implode(', ', $tmp);
				} else if (is_string($order))
					return $order;
				else
					return false;
			} else
				return false;
		}

		private function sql_single_limit($limit = null) {
			if (!is_null($limit)) {
				if (is_int($limit))
					return intval($limit);
				else
					return false;
			} else
				return false;
		}

		private function sql_double_limit($limit = null) {
			if (!is_null($limit)) {
				if ((is_array($limit)) && (count($limit) == 2))
					return implode(', ', $limit);
				else if ((is_string($limit)) || (is_int($limit)))
					return intval($limit);
				else
					return false;
			} else
				return false;
		}

		private function sql_duplicate(array $values, $duplicate = null) {
			if (!is_null($duplicate)) {
				$tmp = array();
				if ((is_array($duplicate)) && (count($duplicate))) {
					foreach ($values as $key => $value)
						if (in_array($key, $duplicate))
							$tmp[] = $this->sql_field($key).' = '.$this->protect($value);
					return implode(', ', $tmp);
				} else if (is_string($duplicate))
					return $this->sql_field($duplicate).' = '.$this->protect($values[$duplicate]);
			} else
				return false;
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
						$ret .= $this->sql_field($item);
				}
			}
			return '('.$ret.')';
		}

		public function status() {
			return $this->status;
		}

		public function transaction_open() {
			if (!is_null($this->db))
				$this->db->block_begin();
		}

		public function transaction_close() {
			if (!is_null($this->db))
				$this->db->block_end();
		}

		public function transaction_cancel() {
			if (!is_null($this->db))
				$this->db->block_cancel();
		}

		public function last_query() {
			return $this->last_query;
		}

		public function last_error() {
			if (!is_null($this->db))
				return $this->db->last_error();
			else
				return false;
		}

		public function last_id() {
			if (!is_null($this->db))
				return $this->db->last_insert_id();
			else
				return false;
		}

		public function count($resource = false) {
			if (!is_null($this->db)) {
				if ($this->select) {
					if ($resource)
						return $this->db->num_rows($resource);
					else
						return false;
				} else
					return $this->db->affected_rows();
			} else
				return false;
		}

		public function next($resource = false) {
			if ($resource)
				return $this->db->fetch_assoc($resource);
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
			$this->select = false;
			if (!is_null($this->db)) {
				if (!is_null($command)) {
					$this->last_query = $command;
					return $this->db->query($command);
				} else
					return false;
			} else
				return false;
		}

		public function select($table = '', $fields = null, $join = null, $condition = null, $group = null, $having = null, $order = null, $limit = null, $raw = null) {
			$this->select = true;
			if (!is_null($this->db)) {
				$table = trim($table);
				if ($table != '') {
					$sql_fields = $this->sql_field($fields);
					$sql_join = $this->sql_join($join);
					$sql_condition = $this->sql_condition($condition);
					$sql_group = $this->sql_group($group);
					$sql_having = $this->sql_having($having);
					$sql_order = $this->sql_order($order);
					$sql_limit = $this->sql_double_limit($limit);

					$sql = 'SELECT '.$sql_fields.' FROM `'.$this->prefix.$table.'`';
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
			$this->select = false;
			if (!is_null($this->db)) {
				$table = trim($table);
				if ($table != '') {
					if ((!is_null($values)) && (is_array($values)) && (count($values))) {
						$f = array();
						$v = array();
						foreach ($values as $key => $value) {
							$f[] = $this->sql_field($key);
							$v[] = $this->protect($value);
						}
						$sql_duplicate = $this->sql_duplicate($values, $duplicate);

						$sql = 'INSERT INTO `'.$this->prefix.$table.'` ';
						$sql .= '('.implode(', ', $f).')';
						$sql .= ' VALUES ';
						$sql .= '('.implode(', ', $v).')';
						if ($sql_duplicate)
							$sql .= ' ON DUPLICATE KEY UPDATE '.$sql_duplicate;
						if (!is_null($raw))
							$sql .= ' '.$raw;
						$this->last_query = $sql;
						return $this->db->query($sql);
					} else
						return false;
				} else
					return false;
			} else
				return false;
		}

		public function delete($table = '', $condition = null, $limit = null, $raw = null) {
			$this->select = false;
			if (!is_null($this->db)) {
				$table = trim($table);
				if ($table != '') {
					$sql_condition = $this->sql_condition($condition);
					$sql_limit = $this->sql_single_limit($limit);

					$sql = 'DELETE FROM `'.$this->prefix.$table.'`';
					if ($sql_condition)
						$sql .= ' WHERE '.$sql_condition;
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

		public function update($table = '', $fields = null, $condition = null, $limit = null, $raw = null) {
			$this->select = false;
			if (!is_null($this->db)) {
				$table = trim($table);
				if ($table != '') {
					if ((!is_null($fields)) && (is_array($fields)) && (count($fields))) {
						$u = array();
						foreach ($fields as $key => $value)
							$u[] = $this->sql_field($key).' = '.$this->protect($value);

						$sql_condition = $this->sql_condition($condition);
						$sql_limit = $this->sql_single_limit($limit);

						$sql = 'UPDATE `'.$this->prefix.$table.'`';
						$sql .= ' SET ';
						$sql .= implode(', ', $u);
						if ($sql_condition)
							$sql .= ' WHERE '.$sql_condition;
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
			} else
				return false;
		}
	}

?>
