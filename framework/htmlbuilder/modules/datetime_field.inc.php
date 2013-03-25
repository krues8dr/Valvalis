<?php

	#####
	## HTML date_field Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: 
	##   Displays a datetime field.  
	##
	## Important: 
	##   Input type subclass names are in lowercase!
	##   This is to make types easily selected.
	##
	##   This is a compound type.
	##
	## Change Log:
	##   08.29.06 - Created file, copied body from date_field object.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	require_once(HTMLBUILDER_MODULE_DIR.'date_field.inc.php');
	require_once(HTMLBUILDER_MODULE_DIR.'time_field.inc.php');
	
	class datetime_field extends BaseInputType {
	
		function datetime_field($args = array()) {
			$this->BaseInputType($args);
			
			if(!$args['datetime_divider']) {
				$this->args['datetime_divider'] = ' ';
			}
		}
		
		function show($args) {
			foreach($args as $arg => $value) {
				$this->args[$arg] = $args[$arg];
			}
			
			$time_field = new time_field($this->args);
			$date_field = new date_field($this->args);
			
			$time_args = $args;
			$date_args = $args;
			
			if(strpos($args['value'], $this->args['datetime_divider']) !== false) {
				list($date_args['value'], $time_args['value']) = split($this->args['datetime_divider'], $args['value']);
			}
			
			$tag .= $date_field->show($date_args);

			$tag .= $time_field->show($time_args);
			
			return $tag;
		}
		
		function preformatData($args) {
			$data = $args['data'];
			$field = $args['field'];
			
			$time_field = new time_field($this->args);
			$date_field = new date_field($this->args);
			
			$time_args = $args;
			$date_args = $args;
			
			$time_data = $time_field->preformatData($time_args);
			$date_data = $date_field->preformatData($date_args);
			$data[$field] = $date_data[$field].$this->args['datetime_divider'].$time_data[$field];
			
			return $data;
		}
		
		function cleanData($args) {
			$object =& $args['object'];
			$value = $args['value'];

			if(strtolower($value) == 'now()') {
				$return_val = $value;
			}
			else {
				$return_val = $object->db_quote($value);
			}
			
			return $return_val;
		}
	}
	
?>