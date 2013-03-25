<?php

	###
	# force_download()
	#
	# Author: Bill Hunt <bill@krues8dr.com>
	#
	# Purpose:
	# Forces the output specified to be downloaded by the browser.
	#
	# Usage: First argument is the data to be output, second argument 
	# is an optional filename for the file to be downloaded.
	#
	# Change Log:
	# 02.08.06 - Created function from PHP.net documentation:
	#
	###
	function force_download($data, $filename = null) {
		if(!strlen($filename)) { $filename = 'export.txt'; }
		$size = strlen($data);
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".$filename."\";");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$size);
		print $out;
	}
	
?>