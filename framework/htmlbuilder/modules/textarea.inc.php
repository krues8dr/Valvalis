<?php

	#####
	## HTML textarea Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: Displays an HTML textarea. 
	##
	## Important: 
	## Input type subclass names are in lowercase!
	## This is to make types easily selected.
	##
	## Change Log:
	## 07.03.05 - Created file, copied body from HTML object.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	
	class textarea extends BaseInputType {
	
		function textarea($args = array()) {
			$this->BaseInputType($args);
		}
		
		function show($args) {
			$this->args = $args;
			
			$tag = '<textarea';
			
			foreach($args as $key=>$value) {
				if($key != 'value') {
			
					// escape doublequotes in value.
					$value = str_replace('"', '\\"', $value);
					
					$tag .= ' '.$key.'="'.$value.'"';
				}
			}
			$tag .= '>';
			$tag .= $args['value'];
			$tag .= '</textarea>';
						
			return $tag;		
		}
		
		function cleanData($args) {
			$object =& $args['object'];
			$value = $args['value'];
			
			$return_val = $object->db_quote($value);
			
			return $return_val;
		}
		
	}
	
?>