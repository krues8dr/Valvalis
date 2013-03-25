<?php

function generate_random_password() {

  // Generate a random number using the Mersenne Twister.
  $rand_number = mt_rand(1, 99999999);

  // Generate a random start position.
  // This must be in the set (0..35) (inclusive) because
  // the base64 encoding of the md5 hash returns a 44 
  // character string ending with '=', and the password 
  // is an eight-character string.
  $rand_start = mt_rand(0, 35);

  // Generate the md5 hash of the random number.
  $rand_text = md5($rand_number);

  // base64 encode the text to make it more user-friendly
  // than hexadecimal (more letters, less numbers).
  $encoded_text = base64_encode($rand_text);

  // Choose a random section of the hash. 
  $rand_password = substr($encoded_text, $rand_start, 8);

  // Return the password.
  return $rand_password;
  
}

?>