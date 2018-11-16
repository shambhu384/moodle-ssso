<?php

if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');
}


function ssso_get_cookie_data($key, $salt, $username, $ip, $expiry, $email) {
  $data = '';
  $data .= 'username=' .$username. '|';
  $data .= 'email=' .$email. '|';
  /* $data .= 'IP=' .$ip. '|'; */
  $data .= 'IP=' .$ip;
  /* $data .= 'expiry=' .$expiry; */

  /* // Encrypt-decrypt using openssl */
  /* $enc_val = encrypt_openssl($key, $salt, $data); */
  /* debugging('Openssl Key: ' .$key); */
  /* debugging('Openssl Salt: ' .$salt); */
  /* debugging('Openssl Plain: ' .$data); */
  /* debugging('Openssl Encrypted: ' .$enc_val); */
  /* debugging('Openssl Decrypted: ' .decrypt_openssl($key, $salt, $enc_val)); */

  // Encrypt-decrypt using mcrypt
  $enc_val = encrypt_RJ256($key, $salt, $data);
  /* debugging('AES Key: ' .$key); */
  /* debugging('AES Salt: ' .$salt); */
  /* debugging('AES Plain: ' .$data); */
  /* debugging('AES Encrypted: ' .$enc_val); */
  /* debugging('AES Decrypted: ' .decrypt_RJ256($key, $salt, $enc_val)); */

  return $enc_val;
}


function encrypt_openssl($key, $salt, $data) {
  $enc_val = openssl_encrypt($data, 'des-cbc', $key, false);
  $retval = trim($enc_val);
  return $retval;
}


function decrypt_openssl($key, $salt, $data) {
  $dec_val = openssl_decrypt($data, 'des-cbc', $key, false);
  $retval = trim($dec_val);
  return $retval;
}


function encrypt_RJ256($key, $salt, $data) {
  $enc_val = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data,
                            MCRYPT_MODE_CBC, $salt);
  $retval = base64_encode($enc_val);
  return $retval;
}


function decrypt_RJ256($key, $salt, $data) {
  $data = base64_decode($data);
  $dec_val = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data,
                            MCRYPT_MODE_CBC, $salt);
  $retval = rtrim($dec_val, "\0\4");
  return $retval;
}
