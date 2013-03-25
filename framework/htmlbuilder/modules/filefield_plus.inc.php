<?php

	#####
	## HTML filefield_plus Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: Displays an HTML file field. 
	##
	## Important: 
	## Input type subclass names are in lowercase!
	## This is to make types easily selected.
	##
	## Change Log:
	## 07.03.05 - Created file, copied body from HTML object.
	#####
	
	require_once(HTMLBUILDER_MODULE_DIR.'filefield.inc.php');
	require_once(HTMLBUILDER_MODULE_DIR.'checkbox.inc.php');
	
	class filefield_plus extends filefield {
	
		function filefield_plus($args = array()) {
			$this->filefield($args);
		}
		
		function show($args) {
			$br = '<br'.($this->xhtml ? ' /' : '').'>';
			
			$tag .= $args['value'] . $br.$br;
			$args['type'] = 'filefield';
			
			$checkbox = new checkbox(
				array(
					'xhtml' => $this->xhtml
				)
			);
			
			$tag .= $checkbox->show(
				array(
					'name' => 'remove_file[]',
					'value' => $args['name']
				)
			) . 'Remove this file' . $br;
			
			$filefield = new filefield(
				array(
					'xhtml' => $this->xhtml
				)
			);
			
			$tag .= $filefield->show($args);
			
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