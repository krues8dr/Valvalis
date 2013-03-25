<?php

	###
	# proper_strrpos()
	#
	# Author: Bill Hunt <bill@krues8dr.com>
	#
	# Purpose:
	# In PHP 4, $needle can only be a single character in
	# the function strrpos.  We write our own, here, to deal
	# with this.	
	#
	# Usage: First argument is the haystack, second argument is the needle.
	#
	# Change Log:
	# 05.23.05 - Imported function
	###
	function proper_strrpos($haystack,$needle){
		while(($ret = strpos($haystack,$needle,$position)) !== false) {
			$position += $ret + strlen($needle);
			$prev_ret = $ret;
		}
		return $prev_ret;
	}

?>