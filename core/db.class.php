<?php
	class DB {
		private $db = null, $online = false, $user, $pass, $host, $base, $pref, $mysqli, $debug, $file;

		public function __construct(array $cfg) {
			if (count($cfg)) {
				if ((isset($cfg['user'])) && (!is_null($cfg['user'])) && ($cfg['user'] != ''))
					$this->user = $cfg['user'];
				else
					$this->user = '';

				if ((isset($cfg['pass'])) && (!is_null($cfg['pass'])) && ($cfg['pass'] != ''))
					$this->pass = $cfg['pass'];
				else
					$this->pass = '';

				if ((isset($cfg['host'])) && (!is_null($cfg['host'])) && ($cfg['host'] != ''))
					$this->host = $cfg['host'];
				else
					$this->host = '';

				if ((isset($cfg['base'])) && (!is_null($cfg['base'])) && ($cfg['base'] != ''))
					$this->base = $cfg['base'];
				else
					$this->base = '';

				if ((isset($cfg['impr'])) && (!is_null($cfg['impr'])) && ($cfg['impr'] != ''))
					$this->mysqli = $cfg['impr'];
				else
					$this->mysqli = false;

				if ((isset($cfg['debg'])) && (!is_null($cfg['debg'])) && ($cfg['debg'] != ''))
					$this->debug = $cfg['debg'];
				else
					$this->debug = false;

				$this->file = dirname(__FILE__).'/'.__CLASS__.'.log';
				$this->addLog('construct('.$this->user.', '.$this->pass.', '.$this->base.', '.$this->host.')');
			} else
				die(__CLASS__.' config array is empty'."\n");
		}

		public function __destruct() {
			$this->addLog('destruct()');
			$this->disconnect();
		}

		private function addLog($data) {
			if ($this->debug)
				@file_put_contents($this->file, date('d/m/Y H:i:s').' '.$data."\r\n", FILE_APPEND) or die($this->file);
		}

		public function setDebugFile($file) {
			$this->file = $file;
		}

		public function getDebugFile($file) {
			return $this->file;
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
				$this->addLog('blockBegin()');
				if ($this->mysqli)
					$this->db->autocommit(false);
				else
					$this->Query('BEGIN');
			}
		}

		public function blockCancel() {
			if ($this->db) {
				$this->addLog('blockCancel()');
				if ($this->mysqli) {
					$this->db->rollback();
					$this->db->autocommit(true);
				} else
					$this->Query('ROLLBACK');
			}
		}

		public function blockEnd() {
			if ($this->db) {
				$this->addLog('blockEnd()');
				if ($this->mysqli) {
					$this->db->commit();
					$this->db->autocommit(true);
				} else
					$this->Query('COMMIT');
			}
		}

		public function connect() {
			$this->addLog('connect('.$this->host.', '.$this->user.', '.$this->pass.', '.$this->base.')');
			if ($this->mysqli) {
				$this->db = new mysqli($this->host, $this->user, $this->pass, $this->base);
				if (!$this->db->connect_error) {
					$this->addLog('set-charset: utf8');
					if (@$this->db->set_charset('utf8')) {
						$this->online = true;
						return true;
					} else {
						if ($this->debug)
							$this->addLog('error: '.$this->LastError());
						$this->disconnect();
						return false;
					}
				} else {
					if ($this->debug)
						$this->addLog('error: '.$this->LastError());
					$this->disconnect();
					return false;
				}
			} else {
				$this->db = @mysql_connect($this->host, $this->user, $this->pass, TRUE, MYSQL_CLIENT_COMPRESS);
				if ($this->db) {
					$this->addLog('select: '.$this->base);
					if (@mysql_select_db($this->base, $this->db)) {
						$this->addLog('set-charset: utf8');
						if (@mysql_set_charset('utf8', $this->db)) {
							$this->online = true;
							return true;
						} else {
							if ($this->debug)
								$this->addLog('error: '.$this->LastError());
							$this->disconnect();
							return false;
						}
					} else {
						if ($this->debug)
							$this->addLog('error: '.$this->LastError());
						$this->disconnect();
						return false;
					}
				} else {
					if ($this->debug)
						$this->addLog('error: '.$this->LastError());
					return false;
				}
			}
		}

		public function lastError() {
			if ($this->db) {
				$this->addLog('lastError()');
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
				$this->addLog('query: '.$query);
				if ($q)
					return $q;
				else {
					if ($this->debug) 
						if ($this->mysqli)
							$this->addLog('error: '.@$this->db->error);
						else
							$this->addLog('error: '.@mysql_error($this->db));
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
				$this->addLog('seek: '.$ret);
				return $ret;
			}
		}

		public function lastInsertID() {
			if ($this->db) {
				$this->addLog('lastInsertID()');
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
				$this->addLog('numRows: '.$ret);
				return $ret;
			}
		}

		public function affectedRows() {
			if ($this->db) {
				if ($this->mysqli)
					$ret = @$this->db->affected_rows;
				else
					$ret = @mysql_affected_rows($this->db);
				$this->addLog('affectedRows: '.$ret);
				return $ret;
			} else
				return false;
		}

		public function fetchRow($resource) {
			$this->addLog('fetchRow()');
			if ($this->mysqli)
				return @$resource->fetch_row();
			else
				return @mysql_fetch_row($resource);
		}

		public function fetchAssoc($resource) {
			$this->addLog('fetchAssoc()');
			if ($this->mysqli)
				return @$resource->fetch_assoc();
			else
				return @mysql_fetch_assoc($resource);
		}

		public function free($resource) {
			$this->addLog('free()');
			if ($this->mysqli)
				@$resource->free();
			else
				@mysql_free_result($resource);
		}

		public function quote($data) {
			$this->addLog('quote: '.$data);
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
				$this->addLog('disconnect()');
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
