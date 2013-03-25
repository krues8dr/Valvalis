<?php

	#####
	## HTML checkbox Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: Displays an HTML checkbox. 
	##
	## Important: 
	## Input type subclass names are in lowercase!
	## This is to make types easily selected.
	##
	## Change Log:
	## 07.10.05 - Created file, copied body from HTML object.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	
	class checkbox extends BaseInputType {
	
		function checkbox($args = array()) {
			$this->BaseInputType($args);
		}
		
		function show($args) {
			$xhtml = $args['xhtml'];
			unset($args['xhtml']);
			
			$this->args = $args;
		
			if($args['object']) { 
				unset($args['object']);
			}
		
			$args['type'] = 'checkbox';
			
			$tag = '<input';
			
			if(!$args['id'] && $args['label'] && $args['name'] && strlen($args['value'])) {
				$args['id'] = str_replace(array('[',']'), array('',''), $args['name']).'_'.$args['value'];
			}
						
			$reserved_args = array('checked', 'label');
			$other_args = array_diff(array_keys($args), $reserved_args);
			
			foreach($other_args as $key) {
				$value = $args[$key];
				
				// escape doublequotes in value.
				$value = str_replace('"', '\\"', $value);
				
				$tag .= ' '.$key.'="'.$value.'"';
			}
			
			
			if($args['checked']) {
				$tag .= ' checked';
				if($xhtml) {
					$tag .= '="checked"';
				}
			}
			
			if($this->xhtml) {
				$tag .= ' /';
			}
			
			$tag .= '>'.NL;
			
			if($args['label']) {
				if($args['id']) {
					$tag .= '<label for="'.$args['id'].'">';
				}
				
				$tag .= $args['label'];
				
				if($args['id']) {
					$tag .= '</label>';
				}
			}
			
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