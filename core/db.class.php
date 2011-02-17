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
				die(__CLASS__.': config array is empty'."\n");
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

		public function setDebug($state) {
			$this->debug = $state;
		}

		public function getDebug() {
			return $this->debug;
		}

		public function getStatus() {
			return $this->online;
		}

		public function blockBegin() {
			if ($this->db) {
				$this->log->add('blockBegin()');
				if ($this->mysqli)
					$this->db->autocommit(false);
				else
					$this->query('BEGIN');
			}
		}

		public function blockCancel() {
			if ($this->db) {
				$this->log->add('blockCancel()');
				if ($this->mysqli) {
					$this->db->rollback();
					$this->db->autocommit(true);
				} else
					$this->query('ROLLBACK');
			}
		}

		public function blockEnd() {
			if ($this->db) {
				$this->log->add('blockEnd()');
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
				$this->connectMysqli();
			else
				$this->connectMysql();
		}

		private function connectMysqli() {
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
		
		private function connectMysql() {
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
		
		public function lastError() {
			if ($this->db) {
				$this->log->add('lastError()');
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

		public function lastInsertID() {
			if ($this->db) {
				$this->log->add('lastInsertID()');
				if ($this->mysqli)
					return $this->db->insert_id;
				else {
					$q = $this->query('SELECT LAST_INSERT_ID()');
					$d = $this->fetch_assoc($q);
					return $d['LAST_INSERT_ID()'];
				}
			}
		}

		public function numRows($resource) {
			if ($this->db) {
				if ($this->mysqli)
					$ret = @$resource->num_rows;
				else
					$ret = @mysql_num_rows($resource);
				$this->log->add('numRows: '.$ret);
				return $ret;
			}
		}

		public function affectedRows() {
			if ($this->db) {
				if ($this->mysqli)
					$ret = @$this->db->affected_rows;
				else
					$ret = @mysql_affected_rows($this->db);
				$this->log->add('affectedRows: '.$ret);
				return $ret;
			} else
				return false;
		}

		public function fetchRow($resource) {
			$this->log->add('fetchRow()');
			if ($this->mysqli)
				return @$resource->fetch_row();
			else
				return @mysql_fetch_row($resource);
		}

		public function fetchAssoc($resource) {
			$this->log->add('fetchAssoc()');
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
