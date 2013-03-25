<?php

	// Try and intelligently require the make_directory function
	if(!function_exists('make_directory')) {
		if(file_exists('./make_directory.php')) {
			require_once('./make_directory.php');
		}
		else {
			trigger_error('File write_file.php included without make_directory.php', E_USER_WARNING);
		}
	}

	If(!function_exists('file_put_contents')) {
		function file_put_contents($n,$d) {
			$f=@fopen($n,"w");
			if (!$f) {
				return false;
			} else {
				fwrite($f,$d);
				fclose($f);
				return true;
			}
		}
	}

	###
	# write_file()
	#
	# Author: Bill Hunt (bill@krues8dr.com)
	#
	# Purpose: Writes a file.  Recursively creates directories as needed.
	#
	# Usage: First argument is the full file path.  Second argument is the file 
	#        data.  Third argument is an optional permission mode (octal).
	#
	# IMPORTANT: requires make_directory() function!!!
	#
	# Change Log:
	# 07.15.06 - Stole function from PHP.net.
	###

	function write_file($file, $data, $mode = 0666) {
		// Attempt to create the directory if it doesn't exist;
		$directory = dirname($file);
		if(!file_exists($directory)) {
			$dir_exists = make_directory($directory);
		}
		else {
			$dir_exists = true;
		}
		
		if($dir_exists) {
			if(file_put_contents($file, $data)) {
				if(chmod($file, $mode)) {
					$return_value = true;
				}
				else {
					trigger_error('Cannot chmod file "'.$file.'" to '.$mode, E_USER_WARNING);
					$return_value = true;
				}
			}
			else {
				trigger_error('Cannot write file "'.$file.'"', E_USER_ERROR);
				$return_value = false;
			}
		}
		else {
			trigger_error('Directory "'.$directory.'" does not exist to write file "'.$file.'"', E_USER_ERROR);
			$return_value = false;
		}
	
	}
	
?>