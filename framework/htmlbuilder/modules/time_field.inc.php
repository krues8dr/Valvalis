<?php

	#####
	## HTML time_field Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: 
	##   Displays a time field.  
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
	require_once(HTMLBUILDER_MODULE_DIR.'select_list.inc.php');
	
	class time_field extends BaseInputType {
	
		function time_field($args = array()) {
			$this->BaseInputType($args);
			
			if(!$args['time_divider']) {
				$this->args['time_divider'] = ':';
			}
			
			if(!($args['minute_increment'])) {
				$this->args['minute_increment'] = 1;
			}
			
			if(!($args['second_increment'])) {
				$this->args['second_increment'] = 1;
			}

			
			if(!strlen($args['twelve_hour'])) {
				$this->args['twelve_hour'] = true;
			}

			if(!strlen($args['use_seconds'])) {
				$this->args['use_seconds'] = false;
			}
		}
		
		function show($args) {
			foreach($args as $arg => $value) {
				$this->args[$arg] = $args[$arg];
			}
			
			if($args['value']) {
				if(strpos($args['value'], $this->args['time_divider']) !== false) {
					list($hour, $minute, $second) = split($this->args['time_divider'], $args['value']);
				}
				elseif(strlen($args['value']) == 6) {
					$hour = substr($args['value'], 0, 2);
					$minute = substr($args['value'], 2, 2);
					$second = substr($args['value'], 4, 2);
				}
				
				if($this->args['twelve_hour']) {				
					if($hour > 12) {
						if($hour == 24) {
							$ampm = 'am';
							$hour = 0;
						}
						else {
							$ampm = 'pm';
							$hour -= 12;
						}
					}
					else {
						$ampm = 'am';
					}
				}
			}
		
			$select = new select_list();


			
			// Build hours array.
			if($this->args['twelve_hour']) {
				for($i = 1; $i <= 12; $i++) {
					$hour_values[$i] = $i;
				}
			}
			else {
				for($i = 0; $i <= 23; $i++) {
					$value = sprintf("%02s",  $i);
					$hour_values[$value] = $value;
				}
				$hour_values['00'] = '24';
			}
			
			$hour_args = array(
				'name' => $args['name'] . '____HOUR',
				'values' => $hour_values,
				'value' => $hour
			);
			
			$tag .= $select->show($hour_args);
			
			
			// Build minutes array
			for($i = 0; $i < 60; $i += $this->args['minute_increment']) {
				$value = sprintf("%02s",  $i);
				$minute_values[$value] = $value;
			}
			
			$minute_args = array(
				'name' => $args['name'] . '____MINUTE',
				'values' => $minute_values,
				'value' => $minute
			);
			
			$tag .= $select->show($minute_args);			
			
			
			if($this->args['use_seconds']) {
				// Build seconds array.
				for($i = 0; $i < 60; $i + $this->args['second_increment']) {
					$value = sprintf("%02s",  $i);
					$second_values[$value] = $value;
				}
				
				$second_args = array(
					'name' => $args['name'] . '____SECOND',
					'values' => $second_values,
					'value' => $second
				);
				
				$tag .= $select->show($second_args);	
			}

			
			if($this->args['twelve_hour']) {
				$ampm_values = array(
					'am' => 'AM',
					'pm' => 'PM'
				);
				
				$ampm_args = array(
					'name' => $args['name'] . '____AMPM',
					'value' => $ampm,
					'values' => $ampm_values
				);
				
				$tag .= $select->show($ampm_args);
			}			

			return $tag;		
		}
		
		function preformatData($args) {
			foreach($args as $arg => $value) {
				$this->args[$arg] = $args[$arg];
			}
			
			$data = $args['data'];
			$field = $args['field'];
			
			if(!$data[$field]) {
				if($this->args['twelve_hour'] && $data[$field.'____AMPM'] == 'pm') {
					 $data[$field.'____HOUR'] += 12;
					 if($data[$field.'____HOUR'] == 24) {
					 	$data[$field.'____HOUR'] = '00';
					 }
				}
				
				if(!$data[$field.'____SECOND']) {
					$data[$field.'____SECOND'] = '00';
				}
				
				$time_parts = array(
					'HOUR',
					'MINUTE',
					'SECOND'
				);
				
				foreach($time_parts as $part) {
					$data[$field.'____'.$part] = sprintf("%02s",  $data[$field.'____'.$part]);

				}
				$data[$field] = $data[$field.'____HOUR'] . $this->args['time_divider'] . $data[$field.'____MINUTE'] . $this->args['time_divider'] . $data[$field.'____SECOND'];
				unset($data[$field.'____HOUR']);
				unset($data[$field.'____MINUTE']);
				unset($data[$field.'____SECOND']);
				unset($data[$field.'____AMPM']);
			}
			
			return $data;
		}
		
		function cleanData($args) {
			foreach($args as $arg => $value) {
				$this->args[$arg] = $args[$arg];
			}
			
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