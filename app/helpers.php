<?php

function base32chunk($binaryString, $bits)
{
  $binaryString = chunk_split($binaryString, $bits, ' ');
  if (substr($binaryString, (strlen($binaryString)) - 1)  == ' ') {
    $binaryString = substr($binaryString, 0, strlen($binaryString)-1);
  }
  return explode(' ', $binaryString);
}

/**
 * Encodes into base32
 *
 * @param string $string Clear text string
 * @return string Base32 encoded string
 */
function base32encode($string)
{
  $base32alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=';

  if (strlen($string) == 0) {
    // Gives an empty string
    return '';
  }

  // Convert string to binary
  $binaryString = '';
  foreach (str_split($string) as $s) {
    // Return each character as an 8-bit binary string
    $binaryString .= sprintf('%08b', ord($s));
  }
  // Break into 5-bit chunks, then break that into an array
  $binaryArray = base32chunk($binaryString, 5);
  // Pad array to be divisible by 8
  while (count($binaryArray) % 8 !== 0) {
    $binaryArray[] = null;
  }
  $base32String = '';
  // Encode in base32
  foreach ($binaryArray as $bin) {
    $char = 32;
    if (!is_null($bin)) {
      // Pad the binary strings
      $bin = str_pad($bin, 5, 0, STR_PAD_RIGHT);
      $char = bindec($bin);
    }
    // Base32 character
    $base32String .= $base32alphabet[$char];
  }
  return $base32String;
}


/**
 * Decodes base32
 *
 * @param string $base32String Base32 encoded string
 * @return string Clear text string
 */
function base32decode($base32String)
{
  $base32alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=';

  // Only work in upper cases
  $base32String = strtoupper($base32String);
  // Remove anything that is not base32 alphabet
  $pattern = '/[^A-Z2-7]/';
  $base32String = preg_replace($pattern, '', $base32String);
  if (strlen($base32String) == 0) {
    // Gives an empty string
    return '';
  }
  $base32Array = str_split($base32String);
  $string = '';
  foreach ($base32Array as $str) {
    $char = strpos($base32alphabet, $str);
    // Ignore the padding character
    if ($char !== 32) {
      $string .= sprintf('%05b', $char);
    }
  }
  while (strlen($string) %8 !== 0) {
    $string = substr($string, 0, strlen($string)-1);
  }
  $binaryArray = base32chunk($string, 8);
  $realString = '';
  foreach ($binaryArray as $bin) {
    // Pad each value to 8 bits
    $bin = str_pad($bin, 8, 0, STR_PAD_RIGHT);
    // Convert binary strings to ASCII
    $realString .= chr(bindec($bin));
  }
  return $realString;
}