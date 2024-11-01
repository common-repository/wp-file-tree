<?php
/*
name: JIMcrypt
type: static class 
package: * Classes

description: Uses mcrypt module to encrypt and decrypt
The hex2bin method does not require this module

author:			jimisaacs 
author uri:		http://ji.dd.jimisaacs.com
*/ 


class JIMcrypt {

	/*
	Change secret_key to a unique phrase.  You won't have to remember it later,
	so make it long and complicated.  You can visit http://api.wordpress.org/secret-key/1.0/
	to get a secret key generated for you, or just make something up.
	*/
	private static $secret_key = "toody fruity on rudy";  // Change this to a unique phrase.
		
	/*
	convert binary data to hex data
	compliment to bin2hex()
	return string
	*/
	public static function hex2bin($data) {
		/* checks if data is valid hex digits */
		if(ctype_xdigit($data)) {
			$len = strlen($data);
			return pack("H" . $len, $data);
		}
		return false;
	}
	
	/*
	encrypt with the mcrypt module
	return string
	*/
	public static function encrypt($data) {
		if(!function_exists('mcrypt_module_open')) return false;
		
		$td = mcrypt_module_open (MCRYPT_TripleDES, "", MCRYPT_MODE_ECB, "");
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		$encdata = mcrypt_ecb (MCRYPT_TripleDES, (self::$secret_key), $data, MCRYPT_ENCRYPT, $iv);
		$hextext = bin2hex($encdata);
		return $hextext;
	}
	
	/*
	decrypt with the mcrypt module
	return string
	*/
	public static function decrypt($data) {
		if(!function_exists('mcrypt_module_open')) return false;
		
		$td = mcrypt_module_open (MCRYPT_TripleDES, "", MCRYPT_MODE_ECB, "");
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size ($td), MCRYPT_RAND);
		/* checks if data is valid hex digits */
		$hex = self::hex2bin($data);
		if(!$hex) {
			return $hex;
		}
		$dectext = trim(mcrypt_ecb (MCRYPT_TripleDES, (self::$secret_key), $hex, MCRYPT_DECRYPT, $iv));
		return $dectext;
	}
}
?>