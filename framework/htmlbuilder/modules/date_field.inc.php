<?php

	#####
	## HTML date_field Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: 
	##   Displays a date field.  
	##
	## Important: 
	##   Input type subclass names are in lowercase!
	##   This is to make types easily selected.
	##
	##   This is a compound type.
	##
	## Change Log:
	##   07.10.05 - Created file, copied body from HTML object.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	require_once(HTMLBUILDER_MODULE_DIR.'select_list.inc.php');
	
	class date_field extends BaseInputType {
	
		function date_field($args = array()) {
			$this->BaseInputType($args);
			
			if(!$args['date_divider']) {
				$this->args['date_divider'] = '-';
			}
		}
		
		function show($args) {
			foreach($args as $arg => $value) {
				$this->args[$arg] = $args[$arg];
			}
			
			if($args['value']) {
				if(strpos($args['value'], $this->args['date_divider']) !== false) {
					list($year, $month, $day) = split($this->args['date_divider'], $args['value']);
				}
				elseif(strlen($args['value']) == 8) {
					$year = substr($args['value'], 0, 4);
					$month = substr($args['value'], 4, 2);
					$day = substr($args['value'], 6, 2);
				}
			}
		
			$select = new select_list();

			// Build months array.
			$months = array(
				'01'  => 'January',
				'02'  => 'February',
				'03'  => 'March',
				'04'  => 'April',
				'05'  => 'May',
				'06'  => 'June',
				'07'  => 'July',
				'08'  => 'August',
				'09'  => 'September',
				'10' => 'October',
				'11' => 'November',
				'12' => 'December'
			);
			
			// Build days array.
			for($i = 1; $i <= 31; $i++) { 
				if($i < 10) {
					$days['0'.$i] = $i;
				}
				else {
					$days[$i] = $i;
				}
			}
			
			if($args['two_digit_year']) {
				$year_arg = 'y';			
			}
			else {
				$year_arg = 'Y';
			}
			
			// Build years array.
			if($args['start_year']) {
				$start_year = $args['start_year'];
			}
			else{
				$start_year = date($year_arg) - 2;
			}
			
			if($args['end_year']) {
				$end_year = $args['end_year'];
			}
			else{
				$end_year = date($year_arg) + 10;
			}
			
			for($i = $start_year; $i <= $end_year; $i++) { 
				$years[$i] = $i; 
			}
			
			
			// Set Month
			$month_args = array(
				'values' => $months,
				'value' => $month
			);
			
			if($args['name']) {
				$month_args['name'] = $args['name'].'____MONTH';
			}
			if($args['id']) {
				$month_args['id'] = $args['id'].'____MONTH';
			}
			
			$tag .= $select->show($month_args);
			
			// Set Day
			$day_args = array(
				'values' => $days,
				'value' => $day
			);
			
			if($args['name']) {
				$day_args['name'] = $args['name'].'____DAY';
			}
			if($args['id']) {
				$day_args['id'] = $args['id'].'____DAY';
			}
			
			$tag .= $select->show($day_args);
			
			// Set Year
			$year_args = array(
				'values' => $years,
				'value' => $year
			);
			
			if($args['name']) {
				$year_args['name'] = $args['name'].'____YEAR';
			}
			if($args['id']) {
				$year_args['id'] = $args['id'].'____YEAR';
			}
			
			$tag .= $select->show($year_args);

			return $tag;		
		}
		
		function preformatData($args) {
			foreach($args as $arg => $value) {
				$this->args[$arg] = $args[$arg];
			}
			
			$data = $args['data'];
			$field = $args['field'];
			
			if(!$data[$field]) {
				$data[$field] = $data[$field.'____YEAR'] . $this->args['date_divider'] . $data[$field.'____MONTH'] . $this->args['date_divider'] . $data[$field.'____DAY'];
				unset($data[$field.'____YEAR']);
				unset($data[$field.'____MONTH']);
				unset($data[$field.'____DAY']);
			}
			
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