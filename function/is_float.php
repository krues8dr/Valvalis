<?php

function is_float($number) {
  if(preg_match('/^\d+\.?\d*$/', $number)) {
    return true;
  }
  else {
    return false;
  }
}

?>