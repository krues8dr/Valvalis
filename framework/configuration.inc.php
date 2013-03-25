<?php

	#####
	## Site Configuration File
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	##
	## Purpose: This file contains site-wide preferences.
	##
	## Important: 
	## 
	## Change Log:
	## 07.26.04 - Created file.
	## 06.08.05 - Move all configuration data into Configuration object
	#####
	
	class Configuration {
		var $database;
		var $max_file_upload_size = 50000000;
	
		function Configuration() {
			
			// Connection Arguments
			
			$this->database =  array(
				'type'       => 'mysql',
				'host'       => 'localhost',
				'database'   => 'bill',
				'username'   => 'bill',
				'password'   => 'd1yb3m'
			);
			
		}
		
		function getConnectionArgs() {
			return $this->database;
		}
	
	}

?>
