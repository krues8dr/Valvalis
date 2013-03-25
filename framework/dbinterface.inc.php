<?php

/*
	DBInterface Object
	
	Author: Bill Hunt (bill@krues8dr.com)
	
	Purpose: 
	Wrapper for ADOConnection object
	 
	Change Log:
	01.09.06 - Created from previous DBInterface
	
	Usage Example:
	
	### BEGIN Usage Example
	$dbi = DBInterface::instance($conf);
	
	$result =& $dbi->db_query(
		array(
			'function' => 'select',
			'table' => 'propertylisting_listing',
			'fields' => array('id', 'mls_acct'),
			'limit' => '25'
		)
	);
	
	print 'Count: '.$result->db_numRows().'<br><br>';
	
	while($row = $result->db_fetchRow()) {
		print "$row[id]:$row[mls_acct]<br>";
	}
	### END Usage Example

*/
	
	class DBInterface {
	
		var $db;
		var $query_builder;
		var $conf;
		var $_set_names;
		
		function DBInterface(&$conf) {
			$this->conf =& $conf;
			
			register_shutdown_function(array(&$this, '_DBInterface'));
		}
		
		function _DBInterface() {
			if(is_object($this->db) && $this->db_isConnected()) {
				$this->db_close();
			}
		}
		
		function getDatabaseObject() {
			if(!is_object($this->db)) {
				$db_file_loc = FRAMEWORK_DIR . strtolower($this->conf->database['type']) . '.inc.php';
				
				if(is_file($db_file_loc)) {
					require_once($db_file_loc);
					$db_obj = $this->conf->database['type'];
					$this->db =& new $db_obj($this->conf->database);
				}
				else {
					trigger_error('Database File Not Found: '.$db_file_loc, E_USER_ERROR);
				}
			}
		}
		
		function getQueryBuilder() {
			if(!is_object($this->query_builder)) {
				$query_builder_type = $this->conf->database['type'].'Query';
				
				$query_builder_file_loc = FRAMEWORK_DIR . $this->conf->database['type'] . '_query.inc.php';
				
				if(is_file($query_builder_file_loc)) {
					require_once($query_builder_file_loc);
					$this->query_builder = &new $query_builder_type($this->conf->database);
				}
				else {
					trigger_error('Query Builder File Not Found: '.$query_builder_file_loc, E_USER_ERROR);
				}
			}
		}
		
		// The following method works something like a unique singleton-generator
		// and registry hybrid.  It creates and stores a unique instance of the 
		// class based on it's connection arguments.
		
		// In this case, it can be used to get a unique singleton database object
		// for each connection.
		
		function &instance(&$conf) {
			static $registry;

			$param_hash = md5(serialize($conf->getConnectionArgs()));
			
			if($registry[$param_hash] !== null && is_object($registry[$param_hash])) {
				$obj = &$registry[$param_hash];
			}
			else {
				$obj = new DBInterface($conf);
				$registry[$param_hash] = &$obj;
			}
			
			return $obj;
		}
		
		function &getResultObject($result_handle) {
			$result_file_loc = FRAMEWORK_DIR .'dbresult.inc.php';
			
			if(is_file($result_file_loc)) {
				require_once($result_file_loc);

				$this->db_result =& new DBResult($this->db, $result_handle);
			}
			else {
				trigger_error('Result Object File Not Found: '.$result_file_loc, E_USER_ERROR);
			}
			return $this->db_result;
		}

		// db_connect will only connect if the db object says that it's not 
		// already connected.  
		function db_connect() {
			$this->getDatabaseObject();
			
			if(!$this->db_isConnected()) { 
				$this->db->db_connect();
			}
			
			if($this->_set_names) {
				$this->db->db_setNames($this->_set_names);
			}
			else {
				$this->_set_names = $this->db->db_getNames();
			}
		}

		// All query functions call db_connect(), just in case the connection
		// has not been previously established.  
		function &db_query($data) { 
		
			// Although query arguments should be an array,
			// there may be instances where the query builder
			// can't handle the job.
			if(is_array($data)) {
				$this->getQueryBuilder();
				$query = $this->query_builder->build_query($data);
				
				if($data['debug']) {
					print $query;
				}
			}
			elseif(strlen($data)) {
				$query = $data;
			}
			
			$this->db_connect();
			$result_handle = $this->db->db_query($query);
			$result_object = &$this->getResultObject($result_handle);
			if($error = $result_object->db_error()) {
				trigger_error('Database Error: '.$error." in query: ".$query, E_USER_ERROR);
			}
			return $result_object;
		}
		
		function db_unbufferedQuery($query) {
			$this->db_connect();
			return $this->db->db_unbuffered_query($query);
		}
		
		function db_error() {
			return $this->db->db_error();
		}

		function db_errno() {
			return $this->db->db_errno();
		}
		
		function db_fetchRow($result) {
			return $this->db->db_fetchRow($result);
		}
	
		function db_affectedRows($result) {
			return $this->db->db_affectedRows($result);
		}
		
		function db_setResultPosition($result, $index) {
			return $this->db->db_setResultPosition($result, $index);
		}
		
		function db_numRows($result) {
			return $this->db->db_numRows($result);
		}
		
		function db_insertID($result) {
			return $this->db->db_insertID($result);
		}

		function db_freeResult($result) {
			return $this->db->db_freeResult($result);
		}

		function db_escapeString($string) {
			$this->getDatabaseObject();
			return $this->db->db_escapeString($string);
		}
		
		function db_isConnected() {
			return $this->db->db_isConnected();
		}
		
		function db_getDatabase() {
			return $this->db->db_getDatabase();
		}
		
		function db_close() {
			return $this->db->db_close();
		}
		
		function db_quote($data) {
			return '\''.$this->db_escapeString($data).'\'';
			
		}
	}

?>
