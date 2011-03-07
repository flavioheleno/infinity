<?php

	require_once __DIR__.'/log.class.php';

	class DB {
		private $db = null;
		private $online = false;
		private $username;
		private $password;
		private $hostname;
		private $database;
		private $prefix;
		private $mysqli;
		private $debug;
		private $log;

		public function __construct(array $cfg) {
			if (count($cfg)) {
				$this->initialize($cfg);

				$this->log = LOG::singleton('db.log');
				$this->log->add('construct('.$this->username.', '.$this->password.', '.$this->database.', '.$this->hostname.')');
			} else {
				$this->log->add(__CLASS__.': config array is empty');
				exit(__CLASS__.': config array is empty'."\n");
			}
		}

		public function __destruct() {
			$this->log->add('destruct()');
			$this->disconnect();
		}

		private function initialize($cfg) {
			$options = array('username' => '', 'password' => '', 'hostname' => '', 'database' => '', 'mysqli' => false, 'debug' => false);
			
			foreach ($options as $option => $default) {
				if (isset($cfg[$option])) {
					if (!is_null($cfg[$option]) && ($cfg[$option] != ''))
						$this->$option = $cfg[$option];
				} else
					$this->$option = $default;
			}
		}

		public function set_debug($state) {
			$this->debug = $state;
		}

		public function get_debug() {
			return $this->debug;
		}

		public function get_status() {
			return $this->online;
		}

		public function block_begin() {
			if ($this->db) {
				$this->log->add('block_begin()');
				if ($this->mysqli)
					$this->db->autocommit(false);
				else
					$this->query('BEGIN');
			}
		}

		public function block_cancel() {
			if ($this->db) {
				$this->log->add('block_cancel()');
				if ($this->mysqli) {
					$this->db->rollback();
					$this->db->autocommit(true);
				} else
					$this->query('ROLLBACK');
			}
		}

		public function block_end() {
			if ($this->db) {
				$this->log->add('block_end()');
				if ($this->mysqli) {
					$this->db->commit();
					$this->db->autocommit(true);
				} else
					$this->query('COMMIT');
			}
		}

		public function connect() {
			$this->log->add('connect('.$this->hostname.', '.$this->username.', '.$this->password.', '.$this->database.')');
			if ($this->mysqli)
				$this->connect_mysqli();
			else
				$this->connect_mysql();
		}

		private function connect_mysqli() {
			$this->db = new mysqli($this->hostname, $this->username, $this->password, $this->database);
			if (!$this->db->connect_error) {
				$this->log->add('set-charset: utf8');
				if (@$this->db->set_charset('utf8')) {
					$this->online = true;
					return true;
				} else {
					if ($this->debug)
						$this->log->add('error: '.$this->LastError());
					$this->disconnect();
					return false;
				}
			} else {
				if ($this->debug)
					$this->log->add('error: '.$this->LastError());
				$this->disconnect();
				return false;
			}
		}
		
		private function connect_mysql() {
			$this->db = @mysql_connect($this->hostname, $this->username, $this->password, TRUE, MYSQL_CLIENT_COMPRESS);
			if ($this->db) {
				$this->log->add('select: '.$this->database);
				if (@mysql_select_db($this->database, $this->db)) {
					$this->log->add('set-charset: utf8');
					if (@mysql_set_charset('utf8', $this->db)) {
						$this->online = true;
						return true;
					} else {
						if ($this->debug)
							$this->log->add('error: '.$this->LastError());
						$this->disconnect();
						return false;
					}
				} else {
					if ($this->debug)
						$this->log->add('error: '.$this->LastError());
					$this->disconnect();
					return false;
				}
			} else {
				if ($this->debug)
					$this->log->add('error: '.$this->LastError());
				return false;
			}
		}
		
		public function last_error() {
			if ($this->db) {
				$this->log->add('last_error()');
				if ($this->mysqli)
					return @$this->db->error;
				else
					return @mysql_error($this->db);
			}
		}

		public function query($query) {
			if ($this->db) {
				if ($this->mysqli)
					$q = @$this->db->query($query);
				else
					$q = @mysql_query($query, $this->db);
				$this->log->add('query: '.$query);
				if ($q)
					return $q;
				else {
					if ($this->debug) 
						if ($this->mysqli)
							$this->log->add('error: '.@$this->db->error);
						else
							$this->log->add('error: '.@mysql_error($this->db));
					return false;
				}
			} else
				return false;
		}

		public function seek($resource, $count) {
			if ($this->db) {
				if ($this->mysqli)
					$rete = @$resource->data_seek($count);
				else
					$ret = @mysql_data_seek($resource, $count);
				$this->log->add('seek: '.$ret);
				return $ret;
			}
		}

		public function last_insert_id() {
			if ($this->db) {
				$this->log->add('last_insert_id()');
				if ($this->mysqli)
					return $this->db->insert_id;
				else {
					$q = $this->query('SELECT LAST_INSERT_ID()');
					$d = $this->fetch_assoc($q);
					return $d['LAST_INSERT_ID()'];
				}
			}
		}

		public function num_rows($resource) {
			if ($this->db) {
				if ($this->mysqli)
					$ret = @$resource->num_rows;
				else
					$ret = @mysql_num_rows($resource);
				$this->log->add('num_rows: '.$ret);
				return $ret;
			}
		}

		public function affected_rows() {
			if ($this->db) {
				if ($this->mysqli)
					$ret = @$this->db->affected_rows;
				else
					$ret = @mysql_affected_rows($this->db);
				$this->log->add('affected_rows: '.$ret);
				return $ret;
			} else
				return false;
		}

		public function fetch_row($resource) {
			$this->log->add('fetch_row()');
			if ($this->mysqli)
				return @$resource->fetch_row();
			else
				return @mysql_fetch_row($resource);
		}

		public function fetch_assoc($resource) {
			$this->log->add('fetch_assoc()');
			if ($this->mysqli)
				return @$resource->fetch_assoc();
			else
				return @mysql_fetch_assoc($resource);
		}

		public function free($resource) {
			$this->log->add('free()');
			if ($this->mysqli)
				@$resource->free();
			else
				@mysql_free_result($resource);
		}

		public function quote($data) {
			$this->log->add('quote: '.$data);
			if ($data == 'NULL')
				return $data;
			else {
				if ($this->mysqli)
					return $this->db->real_escape_string(trim($data));
				else
					return mysql_real_escape_string(trim($data), $this->db);
			}
		}

		public function disconnect() {
			if ($this->db) {
				$this->log->add('disconnect()');
				$this->online = false;
				if ($this->mysqli)
					@$this->db->close();
				else
					@mysql_close($this->db);
				$this->db = null;
			}
		}
	}
?>
