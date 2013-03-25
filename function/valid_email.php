<?php

function valid_email($email) {
	$return_value = false;
	if(preg_match('/^[A-z\'\.0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{2,4}$/', $email)) {
		$return_value = true;
	}
	
	return $return_value;
}

?>