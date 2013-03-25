<?php

	#####
	## HTML yesno_field Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: 
	##   Displays a "yesno" field.  
	##
	## Important: 
	##   Input type subclass names are in lowercase!
	##   This is to make types easily selected.
	##
	##   This is a compound type.
	##
	## Change Log:
	##   01.13.06 - Created file from date_field class.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	require_once(HTMLBUILDER_MODULE_DIR.'select_list.inc.php');
	
	class yesno_field extends BaseInputType {
	
		function yesno_field($args = array()) {
			$this->BaseInputType($args);
		}
		
		function show($args) {
			$this->args = $args;
			
			$args['values'] = array('' => 'Select...', '1' => 'Yes', '0' => 'No');
			
			$select = new select_list();
			
			$tag .= $select->show($args);

			return $tag;		
		}
		
	}
	
?>