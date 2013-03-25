<?php

function camelize_snake($string) {
	return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
}

?>