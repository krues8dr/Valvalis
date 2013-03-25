<?php

	#####
	## HTML TD Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: td display class.
	##
	## Important: 
	## 1. HTMLBuilder subclass names are in lowercase!
	## 2. XHTML version shows a div.
	##
	## Change Log:
	## 01.10.06 - Created object.
	#####

	class td {
	
		var $args = array();
	
		function td($args = array()) {

		}
	
		function show($args) {
			unset($args['xhtml']);
			
			$tag = '<td';
			
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
			$tag .= '</td>';

			return $tag;
		}
	}
	
?>