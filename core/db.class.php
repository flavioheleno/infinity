<?php
/**
* Basic MySQL abstraction
*
* @version 0.1
* @author Flávio Heleno <flaviohbatista@gmail.com>
* @link http://code.google.com/p/infinity-framework
* @copyright Copyright (c) 2010/2011, Flávio Heleno
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*/

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

		public function __construct(array $cfg) {
			if (count($cfg)) {
				$this->initialize($cfg);
				$this->dbg('construct('.$this->username.', '.$this->password.', '.$this->database.', '.$this->hostname.')');
			} else
				exit(__CLASS__.': config array is empty'."\n");
		}

		public function __destruct() {
			$this->dbg('destruct()');
			$this->disconnect();
		}

		public function __toString() {
			return '('.$this->username.', '.$this->password.', '.$this->database.', '.$this->hostname.')';
		}

		private function initialize($cfg) {
			$options = array(
				'username' => '',
				'password' => '',
				'hostname' => '',
				'database' => '',
				'mysqli' => false,
				'debug' => false
			);
			foreach ($options as $option => $default)
				if ((isset($cfg[$option])) && (!is_null($cfg[$option])) && ($cfg[$option] != ''))
					$this->$option = $cfg[$option];
				else
					$this->$option = $default;
		}

		private function dbg($text) {
			if ($this->debug)
				file_put_contents('db.debug.log', $text, FILE_APPEND | LOCK_EX);
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
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('block_begin()');
			if ($this->mysqli)
				$this->db->autocommit(false);
			else
				$this->query('BEGIN');
			return true;
		}

		public function block_cancel() {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('block_cancel()');
			if ($this->mysqli) {
				$this->db->rollback();
				$this->db->autocommit(true);
			} else
				$this->query('ROLLBACK');
			return true;
		}

		public function block_end() {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('block_end()');
			if ($this->mysqli) {
				$this->db->commit();
				$this->db->autocommit(true);
			} else
				$this->query('COMMIT');
			return true;
		}

		public function connect() {
			$this->dbg('connect('.$this->hostname.', '.$this->username.', '.$this->password.', '.$this->database.')');
			if ($this->mysqli)
				return $this->connect_mysqli();
			else
				return $this->connect_mysql();
		}

		private function connect_mysqli() {
			$this->db = new mysqli($this->hostname, $this->username, $this->password, $this->database);
			if ($this->db->connect_error) {
				if ($this->debug)
					$this->dbg('error: '.$this->last_error());
				return false;
			}
			$this->dbg('set-charset: utf8');
			if (@$this->db->set_charset('utf8')) {
				$this->online = true;
				return true;
			} else {
				if ($this->debug)
					$this->dbg('error: '.$this->last_error());
				$this->disconnect();
				return false;
			}
		}
		
		private function connect_mysql() {
			$this->db = @mysql_connect($this->hostname, $this->username, $this->password, TRUE, MYSQL_CLIENT_COMPRESS);
			if ($this->db === false) {
				if ($this->debug)
					$this->dbg('error: '.$this->last_error());
				return false;
			}
			$this->dbg('select: '.$this->database);
			if (@mysql_select_db($this->database, $this->db)) {
				$this->dbg('set-charset: utf8');
				if (@mysql_set_charset('utf8', $this->db)) {
					$this->online = true;
					return true;
				}
			}
			if ($this->debug)
				$this->dbg('error: '.$this->last_error());
			$this->disconnect();
			return false;
		}
		
		public function last_error() {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('last_error()');
			if ($this->mysqli)
				return @$this->db->error;
			return @mysql_error($this->db);
		}

		public function query($query) {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('query: '.$query);
			if ($this->mysqli)
				$q = @$this->db->query($query);
			else
				$q = @mysql_query($query, $this->db);
			if ($q)
				return $q;
			if ($this->debug) 
				if ($this->mysqli)
					$this->dbg('error: '.@$this->db->error);
				else
					$this->dbg('error: '.@mysql_error($this->db));
			return false;
		}

		public function seek($resource, $count) {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			if ($this->mysqli)
				$rete = @$resource->data_seek($count);
			else
				$ret = @mysql_data_seek($resource, $count);
			$this->dbg('seek: '.$ret);
			return $ret;
		}

		public function last_insert_id() {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('last_insert_id()');
			if ($this->mysqli)
				return $this->db->insert_id;
			$q = @mysql_query('SELECT LAST_INSERT_ID()');
			$d = @mysql_fetch_assoc($q);
			return $d['LAST_INSERT_ID()'];
		}

		public function num_rows($resource) {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			if ($this->mysqli)
				$ret = @$resource->num_rows;
			else
				$ret = @mysql_num_rows($resource);
			$this->dbg('num_rows: '.$ret);
			return $ret;
		}

		public function affected_rows() {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			if ($this->mysqli)
				$ret = @$this->db->affected_rows;
			else
				$ret = @mysql_affected_rows($this->db);
			$this->dbg('affected_rows: '.$ret);
			return $ret;
		}

		public function fetch_row($resource) {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('fetch_row()');
			if ($this->mysqli)
				return @$resource->fetch_row();
			return @mysql_fetch_row($resource);
		}

		public function fetch_assoc($resource) {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('fetch_assoc()');
			if ($this->mysqli)
				return @$resource->fetch_assoc();
			return @mysql_fetch_assoc($resource);
		}

		public function free($resource) {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('free()');
			if ($this->mysqli)
				@$resource->free();
			@mysql_free_result($resource);
		}

		public function quote($data) {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('quote: '.$data);
			if ($data == 'NULL')
				return $data;
			if ($this->mysqli)
				return $this->db->real_escape_string(trim($data));
			return mysql_real_escape_string(trim($data), $this->db);
		}

		public function disconnect() {
			if ((is_null($this->db)) || ($this->db === false))
				return false;
			$this->dbg('disconnect()');
			$this->online = false;
			if ($this->mysqli)
				@$this->db->close();
			else
				@mysql_close($this->db);
			$this->db = null;
		}
	}
