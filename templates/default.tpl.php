<?
/*
name: default
type: template 
package: wp-file-tree/templates

description: 
template object 

properties:
Number	$this->index	-----------	the current template index on the currently loaded page
String	$this->tpl	---------------	the current template name
String	$this->mimetype	-----------	the file mimetype
String	$this->file	---------------	the file path of the file
String	$this->basename	-----------	basename of the full file path
String	$this->uri	---------------	URI to the file directly
String	$this->download_uri	-------	URI to download file
String	$this->contents	-----------	the content of the file

useful public methods:
return String	$this->is_template('name');
template		$this->get_template('name');
String			$this->get_post_key('uri');
String			$this->get_compressed('sting');

refer to Shortcode class for more info on properties and methods that may be called safley from the template.

author:			jimisaacs 
author uri:		http://ji.dd.jimisaacs.com
*/


?>
<div>
    <?php echo $this->contents; ?>
</div>