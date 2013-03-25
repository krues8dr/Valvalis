<?php

function snakeize_camel($string) {
	$parts = preg_split('/([A-Z]+)/', $string, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	
	if(count($parts) % 2 == 0) {
		for($i = 0; $i < count($parts); $i = $i+2) {
			$return_bits[] = strtolower($parts[$i].$parts[$i+1]);
		}
	}
	
	if($return_bits) {
		return join('_', $return_bits);
	}
}

?>