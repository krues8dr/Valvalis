<?php

	###
	# make_directory()
	#
	# Author: bat@flurf.net
	# Source URL: http://us3.php.net/manual/en/function.mkdir.php#63406
	#
	# Purpose: Recursively creates directories.
	#
	# Usage: First argument is the directory.  Second argument is an optional permission mode (octal).
	#
	# Change Log:
	# 07.15.06 - Stole function from PHP.net.
	###

	function make_directory($dir, $mode = 0777) {
		if (is_dir($dir) || @mkdir($dir,$mode)) {
			return TRUE;
		}
		if (!make_directory(dirname($dir),$mode)) {
			return FALSE;
		}
		$return_value = @mkdir($dir,$mode);
		if(!$return_value) {
				trigger_error('Cannot write directory "'.$dir.'"', E_USER_ERROR);
		}
		return $return_value;
	}
	
?>