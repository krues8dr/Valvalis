<?php

	#####
	## HTML select_list_plus Class
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
	##   The javascript in this function should only be included
	##   once per page, so a global solution needs to be created.
	##   (Perhaps an included js list, or a list of functions to 
	##   run on load?)
	## 
	##
	## Change Log:
	##   07.10.05 - Created file, copied body from HTML object.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	require_once(HTMLBUILDER_MODULE_DIR.'select_list.inc.php');
	
	class select_list_plus extends select_list {
		var $new_option_prefix = 'NEWOPTION____';
	
		function select_list_plus($args = array()) {
			$this->BaseInputType($args);
		}
		
		function show($args) {
			$this->args = $args;
			
			if(!$args['id']) {
				trigger_error('select_list_plus module requires an id in the arguments array!', E_USER_WARNING);
			}
			static $select_js_included;
			$select = parent::show($args);
			if(!$select_js_included) {
				$select .= '<SCRIPT language"Javascript" TYPE="text/javascript">
<!--
	function addOption(element_name, option_value) {
		var element = document.getElementById(element_name);
		if(element && option_value.length) {
			var selected_index = element.options.length;
			var option_element = document.createElement(\'option\');
			option_element.text = option_value;
			option_element.setAttribute(\'value\', \''.$this->getNewOptionPrefix($args['new_option_prefix']).'\'+option_value);
			element.appendChild(option_element);
			element.selectedIndex = selected_index;
		}
		return false;
	}
//-->
</SCRIPT>';
				$select_js_included = 1;
			}
			
			$select .= '<a href="#" style="font-weight: bold;" onClick="return addOption(\''.$args['id'].'\', prompt(\'Enter a new value.\'));">+</a>';
			
			return $select;

		}
		
		function getNewOptionPrefix() {
			return $this->new_option_prefix;
		}
		
		function setNewOptionPrefix($prefix) {
			return $this->new_option_prefix = $prefix;
		}
		
	}
	
?>