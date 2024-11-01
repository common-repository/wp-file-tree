<?php
/*
name: Shortcode
type: class 
package: WPFileTree

description: Plugin Shortcode Class
Handles all shortcodes supported by plugin.
Functionality includes both hooks for the_content filter and the shortcode.

shortcode_file_encode() hook that encodes all the file shortcodes.
This is to keep this shortcode compatable while having many other plugins that may filter the_content.
Can get very annoying, so encoding all my shortcodes was the best option for now.
In the future I may have to change this to encoding everything between the shortcode brackets.
OR, just remove support for plugins that filter the_content which have NO regard for the shortcode that may be in there already.
PLEASE, there is shortcode support, use it! Don't eff with the_content, or do it more carefully at least.

The shortcode_file_decode() hook decodes the encoded shortcodes.

author:			jimisaacs 
author uri:		http://ji.dd.jimisaacs.com
*/


class Shortcode {

	/*
	var boolean
	*/
	protected $mcrypt_installed;
	
	/*
	The default [file] shortcode attributes
	var Array
	*/
	protected static $defaults = array('path' => 'undefined' , 'mimetype' => '', 'tpl' => 'default');
	
	/*
	Path where plugin templates are located
	Prepended to template names
	var String
	*/	
	protected $tpl_path;
	
	/*
	Extension to append to plugin names
	var String
	*/
	protected $tpl_ext;
	
	/*
	Current shortcode index within the content
	var number
	*/
	protected $shortcode_index;
	
	/*
	Singleton instance.
	Set as protected to allow extension of the class. To extend simply override the {@link getInstance()}
	var Shortcode
	*/
	protected static $_instance;
    
	/*
	Singleton instance.
	return Shortcode
	*/
	public static function getInstance() {
		if (null == self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/*
	return void
	*/
	public function __construct() {
		$this->mcrypt_installed = function_exists('mcrypt_module_open');
		$this->tpl_path = dirname(__FILE__) . '/../templates/';
		$this->tpl_ext = '.tpl.php'; 
		$this->shortcode_index = 0;
	}
	
	/*
	WordPress Hook - add_filter - the_content
	This function removes all shortcodes temporarily then adds appropriate shortcodes to run immediately.
	param: String of all content to filter passed by WordPress
	return String
	*/
	public function content_shortcode_encode($content) {
		global $shortcode_tags;
		
		/* save shortcodes */
		$tags_saved = $shortcode_tags;
		/* remove all shortcodes */
		remove_all_shortcodes();
		/* shortcodes added here are now the only ones active */
		add_shortcode('file', array($this, 'shortcode_file_encode'));
		add_shortcode('vfile', array($this, 'shortcode_vfile_encode'));
		/* now immediately run these added shortcodes */
		$content = do_shortcode($content);
		/* repopulate with the previous active shortcodes */
		$shortcode_tags = $tags_saved;
		/* add the shortcode hook to decode the shortcode */
		add_shortcode('file', array($this, 'shortcode_file_decode'));
		add_shortcode('vfile', array($this, 'shortcode_file_decode'));
		
		return $content;
	}
	
	/*
	WordPress Hook - add_shortcode - file
	Shortcode filter to encode the shortcode attribute values and content to hexidecimal encoding.
	param: String of all content to filter passed by WordPress
	return String
	*/
	public function shortcode_file_encode($atts) {
		/* filter and set defaults */
		extract(shortcode_atts(self::$defaults, $atts));
		$attstr = '';
		foreach($atts as $n => $v) {
			$attstr .= ' ' . $n . '="' . trim($v) . '"';
		}
		return '[file encoded="' . bin2hex($attstr) . '" /]';
	}
	
	/*
	WordPress Hook - add_shortcode - vfile
	Shortcode filter to encode the shortcode attribute values and content to hexidecimal encoding.
	param: String of all content to filter passed by WordPress
	return String
	*/
	public function shortcode_vfile_encode($atts, $content = null) {
		/* filter and set defaults */
		extract(shortcode_atts(self::$defaults, $atts));	
		$attstr = '';
		foreach($atts as $n => $v) {
			$attstr .= ' ' . $n . '="' . trim($v) . '"';
		}
		return '[vfile encoded="' . bin2hex($attstr) . '"]' . bin2hex($content) . '[/vfile]';
	}
	
	/*
	WordPress Hook - add_shortcode - file
	[file] shortcode
	Please refer to the plugin readme.txt file for shortcode documentation.
	
	This function is used in order to render code on client side.
	param: Array  $atts Array of shortcode attributes
	param: String $content Content value enclosed in shortcode tags
	return String
	*/
	public function shortcode_file_decode($atts, $content = null) {
		/* filter and set defaults */
		$defaults = array('encoded' => 'undefined');
		extract(shortcode_atts($defaults, $atts));
		/* decode the attributes */
		$decoded = JIMcrypt::hex2bin($atts['encoded']); // will return false if not encoded
		if($decoded) {
			/* if decoded, then parse the attributes */
			$atts = shortcode_parse_atts($decoded);
		}
		/* filter and set defaults */
		$atts = shortcode_atts(self::$defaults, $atts);
		
		/* get the filename */
		/* remove unessessary slashes */
		$file = preg_replace('/\/+/', '/', $atts['path']);
			
		/* get mimetype */
		$mime = $atts['mimetype'];
		/* load the mimetype class */
		if(!class_exists('JIMimetype')) {
			require_once('Classes/JIMimetype.php');
		}
		/* if a type exists in the class for the filename use that type
		if no type exists in the class use the default set in the JIMimetype class! */
		if (!JIMimetype::type_exists($mime)) {
			$mime = JIMimetype::get_type($file);
		}
		
		/* build templates array */
		/* remove unessessary whitespace */
		$tplstr = preg_replace('/\s+/', '', $atts['tpl']);
		/* run array_filter */
		$templates = array_filter(explode(',', $tplstr), array($this, 'is_template'));
		/* if no template has been set */
		if(count($templates) <= 0) {
			/* get file extension */			
			$ext = array_pop(explode('.', $file));
			/* check if a template for file extension exists and set it to that */
			$templates = array_filter(array($ext), array($this, 'is_template'));
			/* if still no valid template is found use the default template */
			if(count($templates) <= 0) {
				$templates = array('default');
			}
		}
		
		/* get contents */
		if($file != 'undefined') {
			/* check if there is inline content or not */
			if ($content != null) {
				/* since the filter shortcode_file_encode encoded the content 
				we must decode it here before sending it to the template */
				$contents = JIMcrypt::hex2bin($content);
			} else {
				/* get the full path */
				$filename = get_option("wp_file_tree_base_dir") . $file;
				/* if there is no content within the shortcode 
				then try and load the file's contents instead */
				if(file_exists($filename) && is_readable($filename)) {
					/* read the files contents into the $contents variable */
					$contents = file_get_contents($filename);			
				} else if(file_exists($filename)) {
					$contents = 'file is not readable!';
				} else {
					$contents = 'file does not exist!';
				}
			}
		} else {
			/* don't set contents if the filename is undefined */
			$contents = 'undefined';
		}
		
		/* get uris
		either default or mod_rewrite */
		$uri_type = get_option('wp_file_tree_uri_type');
		$siteurl = get_option('siteurl');
		switch($uri_type) {
			case '' ;
				$uri = $siteurl . '/?wp-file-tree=' . $file;
				$download_uri = $siteurl . '/?wp-file-tree=' . $file . '&download';
			break;
			case 'mod_rewrite' ;
				$uri = $siteurl . '/' . get_option('wp_file_tree_link_base') . '/' . $file;
				$download_uri = $siteurl . '/' . get_option('wp_file_tree_download_base') . '/' . $file;
			break;
		}
		/* remove unessessary slashes from the uris
		May be there if people like trailing slashes at the end of hosts etc. */
		$uri = 'http:/' . ereg_replace('(http://)|(/+)', '/', $uri);
		$download_uri ='http:/' . ereg_replace('(http://)|(/+)', '/', $download_uri);
		
		/* get main ouput */
		$xHtml = '';
		/* build the output from template list */
		for($i=0 ; $i<count($templates) ; $i++) {
			/* if more than one template then increment the ID */
			if($i > 0) {
				$this->shortcode_index++;
			}
			/* Turn on output buffering */
			ob_start();
			/* setup required properties */
			$this->index = $this->shortcode_index;
			$this->tpl = $templates[$i];
			$this->file = $file;
			$this->mimetype = $mime;
			$this->contents = $contents;
			$this->basename = basename($file);
			$this->uri = $uri;
			$this->download_uri = $download_uri;
			/* insert the template */
			$this->get_template($templates[$i]);
			/* end output buffering
			and append output from the buffer to the main output */
			$xHtml .= ob_get_clean();
		}
		
		/* when this function is called increment the ID */
		$this->shortcode_index++;
        
		/* do_shortcode to recursively do any shortcodes that may be within the main output. 
		TODO --	Test to see if shortcodes can live and be parsed correctly in a separate file.
			`	Test to see if having these shortcodes can become an issue */
       // return do_shortcode($xHtml);
		return $xHtml;
    }
	
	/* template object functions */
	
	/*
	Check to see if a template name is a valid template
	returns String to act as a array_filter for the template array
	param: template name
	return String
	*/
	public function is_template($name) {
		$tpl = trim($name);
		if(file_exists($this->tpl_path . $tpl . $this->tpl_ext)) {
			return $tpl;
		}
		return;
	}
	
	/*
	If template name is valid then get the template file!
	param: template name
	return template
	*/
	public function get_template($name) {
		$tpl = trim($name);
		if($this->is_template($tpl)) {
			require($this->tpl_path . $tpl . $this->tpl_ext);
		}
		return;
	}
	
	/*
	Gets a key for the file posting virtual file contents
	return String
	*/
	public function get_post_key($uri, $echo=FALSE) {
		if($this->mcrypt_installed) {
			$k = JIMcrypt::encrypt($uri);
		} else { 
			$k = bin2hex($uri);
		}
		if($echo) {
			return $k;
		} else {
			echo $k;
		}
	}
	/*
	Converts a String to an approprately compressed String for use in this plugin
	param: String to compress
	param: boolean flag to return or echo the output
	return String
	*/
	public function get_compressed($str, $echo=FALSE) {
		if($this->mcrypt_installed) {
			$comp = JIMcrypt::encrypt(gzcompress($str, 9));
		} else { 
			$comp = bin2hex(gzcompress($str, 9));
		}
		if($echo) {
			return $comp;
		} else {
			echo $comp;
		}
	}
}
?>