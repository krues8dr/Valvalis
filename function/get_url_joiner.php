<?php

	###
	# get_url_joiner()
	#
	# Author: Bill Hunt <bill@krues8dr.com>
	#
	# Purpose: quick function to get either an ampersand or questionmark for a url.
	#
	# Usage: First argument is a url.
	#
	# Change Log:
	# 05.21.06 - Created function.
	###
	
	function get_url_joiner($url) {
		if(strlen($url)) {
			if(strpos($url, '?') !== false) {
				$return_value = '&';
			}
			else {
				$return_value = '?';
			}
		}
		else {
			$return_value = false;
		}
		
		return $return_value;
	}
	
?>