<?php

	#####
	## HTML Base Input Type Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: prototype class for input types.
	##
	## Important: 
	## Input type subclass names are in lowercase!
	##
	## Change Log:
	## 07.03.05 - Created file, copied body from HTML object.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');

	class link extends BaseInputType {
	
		var $args = array();
	
		function link($args = array()) {
			$this->BaseInputType($args);
		}
	
		function show($args) {
			$this->args = $args;
			
			$tag = '<a';
			
			$reserved_args = array('text');
			$other_args = array_diff(array_keys($args), $reserved_args);
			
			foreach($other_args as $key) {
				$value = $args[$key];
				
				// escape doublequotes in value.
				$value = str_replace('"', '\\"', $value);
				
				$tag .= ' '.$key.'="'.$value.'"';
			}
			
			$tag .= '>';

			if($args['text']) {
				$tag .= $args['text'];
			}

			$tag .= '</a>';

			return $tag;
		}
	
	}
	
?>