<?php

	// This isn't the most elegant way to handle
	// hierarchical inclusion.  A factory should 
	// probably be created to solve this.
	require_once(FRAMEWORK_DIR.'sql_query.inc.php');

	#####
	## MySQL Query Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	##
	## Purpose: 
	## Extends the SQLQuery class to provide
	## MySQL-specific language.  Used to 
	## isolate the SQL from the PHP code.
	## 
	## Change Log:
	## 01.12.05 - Created Object.
	## 01.23.05 - Added singleton functionality.
	#####
	
	class MySQLQuery extends SQLQuery {
		
		function MySQLQuery() {
			parent::SQLQuery();
			// register_shutdown_function(array(&$this, '_MySQLQuery'));
		}
		
		function _MySQLQuery() {
			// Do nothing.
		}
		
		function &instance($args, $name = 'MySQLQuery') {
			return parent::instance($args, $name);
		}
	}

?>