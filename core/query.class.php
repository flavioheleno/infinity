<?php

	class QUERY {
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

		private $field = array();
		private $alias = array();
		private $join = array();
		private $where = array();
		private $group = array();
		private $having = array();
		private $order = array();
		private $limit = array();
		private $value = array();
		private $duplicate = array();
		private $distinct = false;

		function __construct(array $cfg) {
			if (isset($cfg['prefix']))
				$this->prefix = $cfg['prefix'];
			$this->db = new DB($cfg);
			$this->status = $this->db->connect();
		}

		function __destruct() {
			$this->db->disconnect();
		}

		private function protect_keyword($word) {
			$word = trim($word);
			if (is_numeric($word))
				return $word;
			if (strpos($word, '.') != 0) {
				$tmp = explode('.', $word);
				if ($tmp[1] == '*')
					return '`'.$this->prefix.$tmp[0].'`.*';
				else
					return '`'.$this->prefix.$tmp[0].'`.`'.$tmp[1].'`';
			}
			return '`'.$word.'`';
		}

		private function protect_value($value) {
			if ((is_null($value)) || ($value == 'NULL'))
				return 'NULL';
			if (is_array($value)) {
				$r = array();
				foreach ($value as $item)
					$r[] = $this->protect_value($item);
				return '('.implode(', ', $r).')';
			}
			if (is_numeric($value)) {
				if (is_float($value))
					return floatval($value);
				else
					return intval($value);
			}
			$value = filter_var(trim($value), FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
			return '\''.$this->db->quote($value).'\'';
		}

		private function field($field, $escape = false) {
			$this->field[trim($field)] = $escape;
		}

		public function alias($field, $alias) {
			$this->alias[trim($field)] = trim($alias);
		}

		private function sql_field() {
			if (count($this->field) == 0)
				return '*';
			$r = array();
			foreach ($this->field as $field => $escape) {
				if ($escape)
					$tmp = $this->protect_keyword($field);
				else
					$tmp = $field;
				if (isset($this->alias[$field]))
					$tmp .= ' AS '.$this->alias[$field];
				$r[] = $tmp;
			}
			return implode(', ', $r);
		}

		private function join($table, array $condition, $type = '') {
			$this->join[trim($table)] = array(
				'type' => $type,
				'condition' => $condition
			);
		}

		private function sql_join() {
			if (count($this->join) == 0)
				return false;
			$r = array();
			foreach ($this->join as $table => $info) {
				$info['condition'][0] = $this->protect_keyword($info['condition'][0]);
				$info['condition'][1] = $this->protect_keyword($info['condition'][1]);
				if ($info['type'] != '')
					$r[] = strtoupper($info['type']).' JOIN '.$table.' ON ('.implode(' = ', $info['condition']).')';
				else
					$r[] = 'JOIN '.$table.' ON ('.implode(' = ', $info['condition']).')';
			}
			return implode(' ', $r);
		}

		private function where($field, $comparison, $value, $or = false, $escape = false, $command = false) {
			if (count($this->where))
				if ($or)
					$this->where[] = 'OR';
				else
					$this->where[] = 'AND';
			$this->where[] = array($field, $comparison, $value, $escape, $command);
		}

		private function sql_where() {
			if (count($this->where) == 0)
				return false;
			$r = array();
			foreach ($this->where as $item) {
				if (is_array($item)) {
					if (!$item[4])
						$item[0] = $this->protect_keyword($item[0]);
					unset($item[4]);
					if ($item[3])
						$item[2] = $this->protect_value($item[2]);
					unset($item[3]);
					$r[] = implode(' ', $item);
				} else {
					$item = strtoupper(trim($item));
					if (($item == 'OR') || ($item == 'AND'))
						$r[] = $item;
				}
			}
			return implode(' ', $r);
		}

		public function group() {
			foreach (func_get_args() as $arg)
				if (is_array($arg))
					foreach ($arg as $item)
						$this->group[] = trim($item);
				else if (is_string($arg)) {
					if (strpos($arg, ','))
						foreach(explode(',', $arg) as $item)
							$this->group[] = trim($item);
					else
						$this->group[] = trim($arg);
				}
		}

		private function sql_group() {
			if (count($this->group) == 0)
				return false;
			$r = array();
			foreach ($this->group as $field)
				$r[] = $this->protect_keyword($field);
			return implode(', ', $r);
		}

		private function having($field, $comparison, $value, $or = false) {
			if (count($this->having))
				if ($or)
					$this->having[] = 'OR';
				else
					$this->having[] = 'AND';
			$this->having[] = array($field, $comparison, $value);
		}

		private function sql_having() {
			if (count($this->having) == 0)
				return false;
			$r = array();
			foreach ($this->having as $item) {
				if (is_array($item)) {
					$item[0] = $this->protect_keyword($item[0]);
					if ($item[3])
						$item[2] = $this->protect_value($item[2]);
					unset($item[3]);
					$r[] = implode(' ', $item);
				} else {
					$item = strtoupper(trim($item));
					if (($item == 'OR') || ($item == 'AND'))
						$r[] = $item;
				}
			}
			return implode(' ', $r);
		}

		private function order($field, $type) {
			foreach ($field as $arg)
				if (is_array($arg))
					foreach ($arg as $item)
						$this->order[trim($item)] = strtoupper($type);
				else if (is_string($arg)) {
					if (strpos($arg, ','))
						foreach(explode(',', $arg) as $item)
							$this->order[trim($item)] = strtoupper($type);
					else
						$this->order[trim($arg)] = strtoupper($type);
				}
		}

		private function sql_order() {
			if (count($this->order) == 0)
				return false;
			$r = array();
			foreach ($this->order as $field => $order)
				$r[] = $this->protect_keyword($field).' '.$order;
			return implode(', ', $r);
		}

		public function limit() {
			if (func_num_args() == 1)
				$this->limit[0] = intval(func_get_arg(0));
			else
				foreach (func_get_args() as $item)
					$this->limit[] = intval($item);
		}

		private function sql_limit() {
			if (count($this->limit) == 0)
				return false;
			return implode(', ', $this->limit);
		}

		private function value($field, $value, $escape = false) {
			$this->value[trim($field)] = array($value, $escape);
		}

		private function sql_value() {
			if (count($this->value) == 0)
				return false;
			$r = array();
			foreach ($this->value as $field => $properties)
				if ($properties[1])
					$r[] = $this->protect_keyword($field).' = '.$this->protect_value($properties[0]);
				else
					$r[] = $this->protect_keyword($field).' = '.$properties[0];
			return implode(', ', $r);
		}

		public function duplicate() {
			foreach (func_get_args() as $arg)
				if (is_array($arg))
					foreach ($arg as $item)
						$this->duplicate[] = trim($item);
				else if (is_string($arg)) {
					if (strpos($arg, ','))
						foreach(explode(',', $arg) as $item)
							$this->duplicate[] = trim($item);
					else
						$this->duplicate[] = trim($arg);
				}
		}

		private function sql_duplicate() {
			if ((count($this->value) == 0) || (count($this->duplicate) == 0))
				return false;
			$r = array();
			foreach ($this->duplicate as $field)
				if (isset($this->value[$field])) {
					$properties = $this->value[$field];
					if ($properties[1])
						$r[] = $this->protect_keyword($field).' = '.$this->protect_value($properties[0]);
					else
						$r[] = $this->protect_keyword($field).' = '.$properties[0];
				}
			return implode(', ', $r);
		}

		public function __call($function, $args) {
			$or = false;
			if (preg_match('/^or_/', $function)) {
				$or = true;
				$function = substr($function, 3);
			}
			$not = false;
			if (strpos($function, '_not')) {
				$not = true;
				$function = str_replace('_not', '', $function);
			}
			$command = false;
			if (strpos($function, '_command')) {
				$command = true;
				$function = str_replace('_command', '', $function);
			}
			$escape = true;
			if (preg_match('/_unescaped$/', $function)) {
				$escape = false;
				$function = substr($function, 0, -10);
			}
			if (strpos($function, '_'))
				$pieces = explode('_', $function);
			else
				$pieces = array($function);
			switch ($pieces[0]) {
				case 'clear':
					if (isset($pieces[1]))
						switch ($pieces[1]) {
							case 'field':
								$this->field = array();
								break;
							case 'alias':
								$this->alias = array();
								break;
							case 'join':
								$this->join = array();
								break;
							case 'where':
								$this->where = array();
								break;
							case 'group':
								$this->group = array();
								break;
							case 'having':
								$this->having = array();
								break;
							case 'order':
								$this->order = array();
								break;
							case 'limit':
								$this->limit = array();
								break;
							case 'value':
								$this->value = array();
								break;
							case 'duplicate':
								$this->duplicate = array();
								break;
							case 'distinct':
								$this->distinct = false;
								break;
						}
					else {
						$this->field = array();
						$this->alias = array();
						$this->join = array();
						$this->where = array();
						$this->group = array();
						$this->having = array();
						$this->order = array();
						$this->limit = array();
						$this->value = array();
						$this->duplicate = array();
						$this->distinct = false;
					}
				case 'field':
					foreach ($args as $arg)
						if (is_array($arg))
							foreach ($arg as $item)
								$this->field($item, $escape);
						else {
							if (strpos($arg, ',')) {
								foreach (explode(',', $arg) as $item)
									$this->field($item, $escape);
							} else
								$this->field($arg, $escape);
						}
					break;
				case 'value':
					if (count($args) == 2)
						$this->value($args[0], $args[1], $escape);
					else
						foreach ($args as $item)
							foreach ($item as $field => $value)
								$this->value($field, $value, $escape);
					break;
				case 'order':
					if ((isset($pieces[1])) && ((strtoupper($pieces[1]) == 'ASC') || (strtoupper($pieces[1]) == 'DESC')))
						$this->order($args, $pieces[1]);
					break;
				case 'where':
					if (count($pieces) == 1) {
						if (count($args) == 2)
							$this->where($args[0], ($not ? '!=' : '='), $args[1], $or, $escape, $command);
						else
							$this->where($args[0], $args[1], $args[2], $or, $escape, $command);
					} else
						switch ($pieces[1]) {
							case 'lt':
								$this->where($args[0], ($not ? '>=' : '<'), $args[1], $or, $escape, $command);
								break;
							case 'le':
								$this->where($args[0], ($not ? '>' : '<='), $args[1], $or, $escape, $command);
								break;
							case 'gt':
								$this->where($args[0], ($not ? '<=' : '>'), $args[1], $or, $escape, $command);
								break;
							case 'ge':
								$this->where($args[0], ($not ? '<' : '>='), $args[1], $or, $escape, $command);
								break;
							case 'in':
								$this->where($args[0], ($not ? 'NOT IN' :'IN'), $args[1], $or, $escape, $command);
								break;
							case 'like':
								$this->where($args[0], ($not ? 'NOT LIKE' : 'LIKE'), $args[1], $or, $escape, $command);
								break;
							case 'isnull':
								$this->where($args[0], ($not ? 'IS NOT' : 'IS'), 'NULL', $or, $escape, $command);
								break;
						}
					break;
				case 'having':
					if (count($pieces) == 1) {
						if (count($args) == 2)
							$this->having($args[0], ($not ? '!=' : '='), $args[1], $or);
						else
							$this->having($args[0], $args[1], $args[2], $or);
					} else
						switch ($pieces[1]) {
							case 'lt':
								$this->having($args[0], ($not ? '>=' : '<'), $args[1], $or);
								break;
							case 'le':
								$this->having($args[0], ($not ? '>' : '<='), $args[1], $or);
								break;
							case 'gt':
								$this->having($args[0], ($not ? '<=' : '>'), $args[1], $or);
								break;
							case 'ge':
								$this->having($args[0], ($not ? '<' : '>='), $args[1], $or);
								break;
							case 'in':
								$this->having($args[0], ($not ? 'NOT IN' :'IN'), $args[1], $or);
								break;
							case 'like':
								$this->having($args[0], ($not ? 'NOT LIKE' : 'LIKE'), $args[1], $or);
								break;
						}	
					break;
				case 'left':
				case 'right':
				case 'inner':
				case 'outer':
					if ((isset($pieces[1])) && ($pieces[1] == 'join'))
						$this->join($args[0], $args[1], $pieces[0]);
					break;
				case 'join':
					$this->join($args[0], $args[1]);
					break;
			}
		}

		public function distinct() {
			$this->distinct = true;
		}

		public function status() {
			return $this->status;
		}

		public function transaction_open() {
			if ($this->status)
				$this->db->block_begin();
		}

		public function transaction_close() {
			if ($this->status)
				$this->db->block_end();
		}

		public function transaction_cancel() {
			if ($this->status)
				$this->db->block_cancel();
		}

		public function last_query() {
			return $this->last_query;
		}

		public function last_error() {
			if ($this->status)
				return $this->db->last_error();
			return false;
		}

		public function last_id() {
			if ($this->status)
				return $this->db->last_insert_id();
			return false;
		}

		public function count($resource = false) {
			if (!$this->status)
				return false;
			if ($this->select) {
				if ($resource === false)
					return false;
				return $this->db->num_rows($resource);
			}
			return $this->db->affected_rows();
		}

		public function next($resource = false) {
			if ($resource !== false)
				return $this->db->fetch_assoc($resource);
			return false;
		}

		public function seek($resource = false, $count) {
			if ($resource !== false)
				return $this->db->seek($resource, $count);
			return false;
		}

		public function free($resource = false) {
			if ($resource !== false)
				$this->db->free($resource);
		}

		public function raw($command = null) {
			$this->select = false;
			if (!is_null($command)) {
				$this->last_query = $command;
				return $this->db->query($command);
			}
			return false;
		}

		public function select($table, $raw = null) {
			if (!$this->status)
				return false;
			$table = trim($table);
			if ($table == '')
				return false;
			$this->select = true;
			$sql_fields = $this->sql_field();
			$sql_join = $this->sql_join();
			$sql_where = $this->sql_where();
			$sql_group = $this->sql_group();
			$sql_having = $this->sql_having();
			$sql_order = $this->sql_order();
			$sql_limit = $this->sql_limit();
			if ($this->distinct)
				$sql = 'SELECT DISTINCT '.$sql_fields;
			else
				$sql = 'SELECT '.$sql_fields;
			$sql .= ' FROM `'.$this->prefix.$table.'`';
			if ($sql_join !== false)
				$sql .= ' '.$sql_join;
			if ($sql_where !== false)
				$sql .= ' WHERE '.$sql_where;
			if ($sql_group !== false)
				$sql .= ' GROUP BY '.$sql_group;
			if ($sql_having !== false)
				$sql .= ' HAVING '.$sql_having;
			if ($sql_order !== false)
				$sql .= ' ORDER BY '.$sql_order;
			if ($sql_limit !== false)
				$sql .= ' LIMIT '.$sql_limit;
			if (!is_null($raw))
				$sql .= ' '.$raw;
			$this->last_query = $sql;
			return $this->db->query($sql);
		}

		public function insert($table, $raw = null) {
			if (!$this->status)
				return false;
			$table = trim($table);
			if ($table == '')
				return false;
			$this->select = false;
			if (count($this->value) == 0)
				return false;
			$f = array();
			$v = array();
			foreach ($this->value as $field => $properties) {
				$f[] = $this->protect_keyword($field);
				if ($properties[1])
					$v[] = $this->protect_value($properties[0]);
				else
					$v[] = $properties[0];
			}
			$sql_duplicate = $this->sql_duplicate();

			$sql = 'INSERT INTO `'.$this->prefix.$table.'` ';
			$sql .= '('.implode(', ', $f).')';
			$sql .= ' VALUES ';
			$sql .= '('.implode(', ', $v).')';
			if ($sql_duplicate !== false)
				$sql .= ' ON DUPLICATE KEY UPDATE '.$sql_duplicate;
			if (!is_null($raw))
				$sql .= ' '.$raw;
			$this->last_query = $sql;
			return $this->db->query($sql);
		}

		public function delete($table, $raw = null) {
			if (!$this->status)
				return false;
			$table = trim($table);
			if ($table == '')
				return false;
			$this->select = false;
			$sql_where = $this->sql_where();
			$sql_limit = $this->sql_limit();

			$sql = 'DELETE FROM `'.$this->prefix.$table.'`';
			if ($sql_where !== false)
				$sql .= ' WHERE '.$sql_where;
			if ($sql_limit !== false)
				$sql .= ' LIMIT '.$sql_limit;
			if (!is_null($raw))
				$sql .= ' '.$raw;
			$this->last_query = $sql;
			return $this->db->query($sql);
		}

		public function update($table, $raw = null) {
			if (!$this->status)
				return false;
			$table = trim($table);
			if ($table == '')
				return false;
			$this->select = false;
			$sql_value = $this->sql_value();
			if ($sql_value === false)
				return false;
			$sql_where = $this->sql_where();
			$sql_limit = $this->sql_limit();

			$sql = 'UPDATE `'.$this->prefix.$table.'`';
			$sql .= ' SET ';
			$sql .= $sql_value;
			if ($sql_where)
				$sql .= ' WHERE '.$sql_where;
			if ($sql_limit)
				$sql .= ' LIMIT '.$sql_limit;
			if (!is_null($raw))
				$sql .= ' '.$raw;
			$this->last_query = $sql;
			return $this->db->query($sql);
		}

		public function __toString() {
			$bfr = 'fields ';
			$bfr .= print_r($this->field, true);
			$bfr .= 'join ';
			$bfr .= print_r($this->join, true);
			$bfr .= 'where ';
			$bfr .= print_r($this->where, true);
			$bfr .= 'group ';
			$bfr .= print_r($this->group, true);
			$bfr .= 'having ';
			$bfr .= print_r($this->having, true);
			$bfr .= 'order ';
			$bfr .= print_r($this->order, true);
			$bfr .= 'limit ';
			$bfr .= print_r($this->limit, true);
			$bfr .= 'values ';
			$bfr .= print_r($this->value, true);
			$bfr .= 'duplicate ';
			$bfr .= print_r($this->duplicate, true);
			return $bfr;
		}

	}

?>
