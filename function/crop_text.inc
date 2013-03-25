<?php
function crop_text($string, $length, $ellipsis = '')
{
	if(strlen($string) > $length)
	{
		$space = strpos($string, ' ', $length);
		
		if($space === false || $space > $length * 1.10)
		{
			// if we never find a space, or the space is too far away
			$string = substr($string, 0, $length) . $ellipsis;
		}
		else
		{
			$string = substr($string, 0, $space + 1) . $ellipsis;
		}
	}

	return $string;
}
?>