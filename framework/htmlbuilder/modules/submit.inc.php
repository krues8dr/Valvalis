<?php

	#####
	## HTML Submit Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: Displays an HTML submit field. 
	##
	## Important: 
	## Input type subclass names are in lowercase!
	## This is to make types easily selected.
	##
	## Change Log:
	## 07.03.05 - Created file, copied body from HTML object.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	
	class submit extends BaseInputType {
	
		function submit($args = array()) {
			$this->BaseInputType($args);
		}
		
		function show($args) {
			$this->args = $args;
			
			$args['type'] = 'submit';
			return parent::show($args);
		}
		
	}
	
?>