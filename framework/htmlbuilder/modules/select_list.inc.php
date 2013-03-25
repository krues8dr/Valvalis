<?php

	#####
	## HTML select_list Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: Displays an HTML textfield. 
	##
	## Important: 
	## Input type subclass names are in lowercase!
	## This is to make types easily selected.
	##
	## Change Log:
	## 07.03.05 - Created file, copied body from HTML object.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	
	class select_list extends BaseInputType {
	
		function select_list($args = array()) {
			$this->BaseInputType($args);
		}
		
		function show($args) {
			$this->args = $args;

			unset($args['xhtml']);
			
			$tag = '<select';
			
			foreach($args as $key=>$value) {
				if($key != 'value' && $key != 'values') {
			
					// escape doublequotes in value.
					$value = str_replace('"', '\\"', $value);
					
					$tag .= ' '.$key.'="'.$value.'"';
				}
			}
			$tag .= '>'.NL;

			if($args['values']) {
				foreach($args['values'] as $key => $label) {
					$selected = false;
					
					if(is_array($args['value'])) {
						if(in_array($key, $args['value'])) { $selected = true; }
					}
					else{
						// This gets a bit tricky with 0 == false == ''.
						// We can't do === here, so do the next best thing.
							if($args['value'] == $key && strlen($args['value']) == strlen($key)) { 
								$selected = true; 
							}

					}
					
					$tag .= '<option value="'.$key.'"';
					if($selected) { $tag .= ' selected'; }
					$tag .= '>'.$label.'</option>'.NL;
				}
			}

			$tag .= '</select>'.NL;
						
			return $tag;		
		}
		
		
		function cleanData($args) {
			$object =& $args['object'];
			$value = $args['value'];
			
			if(is_numeric($value)) {
				$return_val = $value;
			}
			else {
				$return_val = $object->db_quote($value);
			}
			
			
			return $return_val;
		}
		
	}
	
?>