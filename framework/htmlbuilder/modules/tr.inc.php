<?php

	#####
	## HTML TR Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: tr display class.
	##
	## Important: 
	## 1. HTMLBuilder subclass names are in lowercase!
	## 2. XHTML version shows a div.
	##
	## Change Log:
	## 01.10.06 - Created object.
	#####

	class tr {
	
		var $args = array();
	
		function tr($args = array()) {

		}
	
		function show($args) {
			unset($args['xhtml']);
		
			$tag = '<tr';
			
			if(is_array($args)) {
				foreach($args as $key=>$value) {
					if($key != 'content') {
						// escape doublequotes in value.
						$value = str_replace('"', '\\"', $value);
						
						$tag .= ' '.$key.'="'.$value.'"';
					}
				}
			}		
			$tag .= '>';
			
			if(is_array($args)) {
				$tag .= $args['content'];
			}
			elseif(strlen($args)) {
				// $args is the content string
				$tag .= $args;
			}
			$tag .= '</tr>';

			return $tag;
		}
	}
	
?>