<?php
/*
name: resource_header
type: script 
package: wp-file-tree

description: get any resource
Check and do a link action for wp-file-tree parameter.
This is the action that acts like a direct URI to the specified file.
If the option parameter of 'mimetype' is sent, and is valid, then that mimetype overwrites the default type of the file extension.
If there is an optional POST parameter of 'contents' sent along with required parameters,
this is now a direct link to a virtual inline file.
		
author:			jimisaacs 
author uri:		http://ji.dd.jimisaacs.com
*/ 


/* check is parameters are set */
if(empty($_REQUEST['wp-file-tree'])) {
	die('Error! Required parameters not set');
}

/* static class JIMimetype */
require_once('Classes/JIMimetype.php');

/* required parameter */
$f = trim($_REQUEST['wp-file-tree']);
/* optional parameter */
$m = trim($_REQUEST['mimetype']);		
/* optional parameter */
$c = trim($_POST['contents']);

$mimetype = (JIMimetype::type_exists($m)) ? $m : JIMimetype::get_type($f);
$filename = $this->base_dir . $f;
		
/*
check if there was file contents already sent with parameters
or if we must attempt to read them
*/
if(!empty($c)) {
	$k = trim($_REQUEST['key']);
	if(empty($k)) {
		die('Error! Posting contents requires a key!');
	}
	if($this->mcrypt_installed) {
		/* this is where the encrypting becomes important, 
		because your secret key inside JIMCrypt is used with some salt */
		$k = JIMcrypt::decrypt($k);
		/* inline contents should have been sent encrypted and gzcompressed */
		$contents = gzuncompress(JIMcrypt::decrypt($c));
	} else {
		/* not much security here, you might just have to use an alternative method */
		$k = JIMcrypt::hex2bin($k);
		/* inline contents should have been sent encoded and gzcompressed */
		$contents = gzuncompress(JIMcrypt::hex2bin($c));
	}
	/* VALIDATE KEY! - should be decrypted to a valid URI */
	if($k != 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) {
		die('Hack much!? The supplied key is invalid!');
	}
	$filesize  = strlen($contents);
} else if(file_exists($filename) && is_readable($filename)) {
	$contents = file_get_contents($filename);
	$filesize  = filesize($filename);
} else if(file_exists($filename)) {
	die('Error! File is not readable!');
} else {
	die('Error! File does not exist!');
}


/* start headers */
header("Pragma: public"); // required 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("Cache-Control: private", false); // required for certain browsers 
header("Content-Transfer-Encoding: binary"); 
header("Content-Type: " . $mimetype);

/* force download headers */
if(isset($_REQUEST['download'])) {
	header("Content-Length: " . $filesize); 
	header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";" );
}

die($contents);
?>