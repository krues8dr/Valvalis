<?php

	require_once('template_page.inc.php');

	####
	## class ValvalisTemplate
	##
	## Author: Bill Hunt (bill.hunt@valvalis.org)
	##
	## Purpose : Replaces tags from a template file with content 
	## passed to the function.
	##
	## Change Log:
	## 10.30.03 - Created file.
	## 06.21.04 - Changed argument list.
	## 10.06.04 - Changed file to die on template file Does Not Exist.
	## 01.11.06 - Created class, adapted function.
	##          - Replaced die() with trigger_error()
	## 06.12.06 - Added php execution and replacement.
	## 05.09.07 - Separated execution into separate object.
	####
	class Template {
		
		var $data = array();
		
		function Template($args = null) {
			$this->__construct($args);
		}
		
		function __construct($args = null) {
		}
		
		// function show()
		// Takes either a 'template_file' argument or 'template',
		// containing the actual template data itself.
		// Takes 'replacements' as a hash of elements to replace.
		// Takes 'data' as variables to set into the TemplatePage class.
		function show($args) {
			if($args['template_file']) {
				$filename = $args['template_file'];		
				if(file_exists($filename)) {
					$file_data = file_get_contents($filename);
				} 
				else { // File DNE.
					//return false;
					trigger_error('Template File does not exist: "'.$filename.'"', E_USER_ERROR);
				}
			}
			elseif($args['template']) {
				$file_data = $args['template'];
			}
			else {
				trigger_error('No template passed to Template class', E_USER_ERROR);
			}
			
			if($args['data']) {
				$data = array_merge($args['data'], $this->data);
			}
			else {
				$data = $this->data;
			}
			
			$page =& new TemplatePage($data);
			$file_data = $page->show($file_data);
			return $file_data;
		}

	}

?>