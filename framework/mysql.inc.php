<?php

	#####
	## MySQL Database Interface Wrapper Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	##
	## Purpose: Provides a generic wrapper for the MySQL DB functions.
	## 
	## Change Log:
	## 07.26.04 - Created file, imported functions from old DBInterface class.
	#####
	
	class MySQL {
		
		var $database;
		var $host;
		var $username;
		var $password;
		var $connected = false;
		var $dbh;
		
		
		function MySQL($args) {
			$this->database = $args['database'];
			$this->host = $args['host'];
			$this->username = $args['username'];
			$this->password = $args['password'];

			register_shutdown_function(array(&$this, '_MySQL'));
		}
	
		function _MySQL() {
			if($this->db_isConnected()) {
				$this->db_close();
			}
		}
		
		function db_connect() {			
			if(!$this->db_isConnected()) {
				$dbh = mysql_connect($this->host, $this->username, $this->password);
				if($dbh) {
					$this->dbh = $dbh;
					$selectdb = mysql_select_db($this->database, $dbh);
					if($selectdb) {
						$this->connected = 1;
						
						$return_value = true;
					}
					else { 
						$return_value = false; 
						trigger_error('Unable to select db '.$this->database, E_USER_ERROR);
					}
				} else { 
					$return_value = false; 
					trigger_error('Unable to connect to db '.$this->username, E_USER_ERROR);
				}
			}
			else { 
				$return_value = true; 
			}
			
			return $return_value;
		}
			
		function db_query($query) {
			return @mysql_query($query);
		}
		
		function db_unbufferedQuery($query) {
			return @mysql_unbuffered_query($query);
		}
		
		function db_fetchRow($result) {
			return @mysql_fetch_array($result, MYSQL_ASSOC);
		}
	
		function db_affectedRows($result) {
			return @mysql_affected_rows($result);
		}
		
		function db_numRows($result) {
			return @mysql_num_rows($result);
		}
		
		function db_setResultPosition($result, $index) {
			return @mysql_data_seek($result, $index);
		}
		
		function db_insertID($result) {
			return mysql_insert_id();
		}

		function db_error() {
			return @mysql_error($this->dbh);
		}

		function db_errno() {
			return @mysql_errno($this->dbh);
		}

		function db_freeResult($result) {
			return @mysql_free_result($result);
		}

		function db_escapeString($string) {
			// If the mysql_real_escape_string function exists, use that.
			if(function_exists('mysql_real_escape_string')) {
				// If magic quotes are on, strip slashes first.
				if(get_magic_quotes_gpc()) {
					$string = stripslashes($string);
				}
				
				// Connect if not connected, so mysql_real_escape_string 
				// can get the character set from the database.  
				$this->db_connect();
				$string = @mysql_real_escape_string($string, $this->dbh);
			}
			else {
				$string = @mysql_escape_string($string);
			}
			return $string;
		}
		
		function db_isConnected() {
			return $this->connected;
		}
		
		function db_getDatabase() {
			return $this->database;
		}
		
		function db_close() {
			@mysql_close($this->dbh);
		}
		
		function db_setNames($encoding) {
			$this->db_connect();
			if(strlen($encoding)) {
				$this->db_query('SET NAMES '.$encoding);
			}
		}
		
		function db_getNames() {
			$this->db_connect();
			return @mysql_client_encoding($this->dbh);
		}
	}
	
?>