<?php

	###
	# redirect()
	#
	# Author: Bill Hunt <bill@krues8dr.com>
	#
	# Purpose: quick function to do a header redirect.
	#
	# Usage: First argument is the new location.  Second argument is an optional message.
	#
	# Change Log:
	# 08.26.04 - Created function.
	###
	
	function redirect($location, $prompt='') {
		if($prompt) {
			$location .= (strpos($location, '?') ? '&' : '?');
			$location .= 'prompt='.urlencode($prompt);
			
		}
		header('Location: '.$location);
	}
	
?>