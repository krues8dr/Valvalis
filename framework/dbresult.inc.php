<?php

/*
	DBResult Object
	
	Author: Bill Hunt (bill@krues8dr.com)
	
	Purpose: 
	Wrapper (Facade) for ADORecordSet object.
	
	 
	Change Log:
	01.09.06 - Created file
*/

	class DBResult {
		var $db;
		var $result;
	
		function DBResult(&$db, &$result) {
			if(!$db) {
				trigger_error('DBResult needs a database to continue.', E_USER_ERROR);
			}
			if($result === null) {
				trigger_error('DBResult needs a result to continue.', E_USER_ERROR);
			}
			$this->db = &$db;
			$this->result = &$result;
			register_shutdown_function(array(&$this, '_DBResult'));
		}
		
		function _DBResult() {
			$this->db->db_freeResult($this->result);
		}
		
		function db_fetchRow() {
			return $this->db->db_fetchRow($this->result);
		}
	
		function db_affectedRows() {
			return $this->db->db_affectedRows($this->result);
		}
		
		function db_numRows() {
			return $this->db->db_numRows($this->result);
		}
		
		function db_insertId() {
			return $this->db->db_insertID($this->result);
		}

		function db_error() {
			return $this->db->db_error();
		}

		function db_errno() {
			return $this->db->db_errno();
		}

		function db_freeResult() {
			return $this->db->db_freeResult($this->result);
		}
	
	}
	
?>