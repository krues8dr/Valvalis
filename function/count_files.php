<?php

	###
	# count_files()
	#
	# Author: Bill Hunt <bill@krues8dr.com>
	#
	# Purpose: counts the number of files (or subdirectories) in a given directory
	#
	# Usage: First argument is directory path.  Second argument is type of file (e.g.: '.jpg', '.gif') to count.  
	# For any type of file, leave out type argument.  To count directories, use 'DIR' as the type.
	#
	# Change Log:
	# 06.21.03 - Created function.
	###
	
	function count_files($directory, $type='') {
		$d = dir($directory); 
		while($entry = $d->read()) {
			if($type!='DIR' && is_file($directory.'/'.$entry) && substr($entry,-strlen($type),strlen($type))==$type) { $entries[] = $entry; }
			elseif(is_dir($directory.'/'.$entry) && substr($entry,0,1)!='.') { $entries[] = $entry; }
		}
		return count($entries);
	}
	
?>