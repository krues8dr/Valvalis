<?php

	#####
	## HTML Label Form Field Type
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: Creates a label field.  Has a hidden value field.
	##
	## Important: 
	## Input type subclass names are in lowercase!
	##
	## Change Log:
	## 05.17.06 - Created file, copied body from HTML object.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	require_once(HTMLBUILDER_MODULE_DIR.'hidden.inc.php');

	class label extends BaseInputType {
	
		var $args = array();
	
		function label($args = array()) {
			$this->BaseInputType($args);
		}
	
		function show($args) {
			$this->args = $args;
			
			$tag = $args['value'];
			$hidden = new Hidden($this->args);
			
			$tag .= $hidden->show(
				array(
					'name' => $args['name'],
					'value' => $args['value']
				)
			);

			return $tag;
		}
	
	}
	
?>