<?php

function escape_rlike($string){
  return preg_replace("/([.\[\]*^\$])/", '\\\$1', $string);
}

?>