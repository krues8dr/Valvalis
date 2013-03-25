<?php

	#####
	## HTML image_field Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: 
	##   Displays an advanced select list.
	##
	## Important: 
	##   Input type subclass names are in lowercase!
	##   This is to make types easily selected.
	##
	##   This is a compound type.
	## 
	##
	## Change Log:
	##   08.24.05 - Created file.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	require_once(HTMLBUILDER_MODULE_DIR.'filefield.inc.php');
	
	class image_field extends filefield {
	
		function image_field($args = array()) {
			$this->filefield($args);
		}
		
		function show($args) {
			$this->args = $args;
			return parent::show($args);

		}
		
		function getExtraContent($args) {
			$object =& $args['object'];
			$filename = $args['value'];
			$field_name = $args['name'];
			
			$file_path = $object->upload_dir . $field_name . '/' . $filename;
			$file_web_path = $object->web_upload_dir . $field_name . '/' . $filename;
			
			// Replace "upload" below with a variable, later.
			if(strlen($filename) && file_exists($file_path)) {
				$file_data = getimagesize($file_path);
				if($file_data['mime'] == 'image/jpeg' || $file_data['mime'] == 'image/gif' || $file_data['mime'] == 'image/png') {
					$out .= '<img src="'.$file_web_path.'" width="'.$file_data[0].'" height="'.$file_data[1].'"><br>';
				}
				else {
					$out .= '<a href="'.$file_web_path.'">'.$filename.'</a><br>';
				}
				
				$out .= '<input type="checkbox" name="remove_file[]" value="'.$field_name.'">Remove';
			}
			return $out;
		}
		
		function showStatic($args) {
			$object =& $args['object'];
			$filename = $args['value'];
			$field_name = $args['name'];
			
			$file_path = $object->upload_dir . $field_name . '/' . $filename;
			$file_web_path = $object->web_upload_dir . $field_name . '/' . $filename;
			
			// Replace "upload" below with a variable, later.
			if(strlen($filename) && file_exists($file_path)) {
				$file_data = getimagesize($file_path);
				if($file_data['mime'] == 'image/jpeg' || $file_data['mime'] == 'image/gif' || $file_data['mime'] == 'image/png') {
					$out .= '<img src="'.$file_web_path.'" width="'.$file_data[0].'" height="'.$file_data[1].'"><br>';
				}
			}
			
			if(!strlen($out)) {
				$out .= $filename;
			}
		
			return $out;
		}
		
		function cleanData($args) {
			$object =& $args['object'];
			$value = $args['value'];
			
			$return_val = $object->db_quote($value);
			
			return $return_val;
		}
	}
	
?>