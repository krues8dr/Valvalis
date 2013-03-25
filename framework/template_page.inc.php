<?php

	####
	## class ValvalisTemplatePage
	##
	## Author: Bill Hunt (bill.hunt@valvalis.org)
	##
	## Purpose : Helper class used within ValvalisTemplate as a wrapper for the page
	## 		Though not entirely necessary, this keeps the data separate.
	##
	## Change Log:
	## 05.09.07 - Created file from ValvalisTemplate.
	####
	class TemplatePage {
		
		var $xml_replacements;
		var $data;
		
		// Takes the data that will be used within the eval() below
		// e.g. $this->rows, $this->count, etc.
		
		function TemplatePage($args = array()) {
			$this->__construct($args);
		}
		
		function __construct($args = array()) {
			// Setup the object
			foreach($args as $key=>$value) {
				$this->$key = $value;
			}
		}
		
		// Takes the actual content of the template, not a path.
		function show($file_data) {
			
			// Before we can eval() html, we must remove any xml tags within it.
			$expression = '/<\\?xml(.*?)\\?>/s';
			// The trailing "s" there lets the "." metachar accept newlines.
			$file_data = preg_replace_callback($expression, array(&$this, 'preg_catch_xml'), $file_data);
			
			// Make the data local.
			
			@extract(get_object_vars($this));
			
			// You can eval() html, but you have to fake out the parser by putting 
			// a closing php tag before it.  Adding a trailing opening tag will cause an error.
			ob_start();
			eval('?>'.$file_data);
			$file_data = ob_get_contents();
			ob_end_clean();
			
			if(is_array($this->xml_replacements) && count($this->xml_replacements)) {
				foreach($this->xml_replacements as $key=>$value) {
					$file_data = str_replace($key, $value, $file_data);
				}
			}
			
			return $file_data;
			
		}
		
		function preg_catch_xml($matches) {
			$index_id = count($this->xml_replacements);
			
			$index_name = '<!--REPLACE_XML_'.$index_id.'-->';
			
			$this->xml_replacements[$index_name] = $matches[0];
			
			return $index_name;
		}
		
	}
	
?>