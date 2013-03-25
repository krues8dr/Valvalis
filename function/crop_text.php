<?php

function crop_text($text, $length, $trailing = '') {
  $text = stripslashes(strlen($text) > $length ? substr($text, 0, strrpos(substr($text, 0, $length-1),' ')) . $trailing : $text);
  return $text;
}

?>