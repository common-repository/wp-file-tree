<?php
/*
name: Messages
type: class
package: WPFileTree

description: Hold methods and the array to display plugin admin messages and notices

author:			jimisaacs
author uri:		http://ji.dd.jimisaacs.com
*/


require_once dirname(__FILE__) . '/../../../../wp-includes/l10n.php';


/* class WPFileTree */
require_once 'WPFileTree.php';


class Messages {
	/*
	Message strings
	var array
	*/
	protected $messages;
	
	/*
	Initializes messages
	return void
	*/
	public function __construct() {
		$this->messages = array(
			'BASE_DIR_REQUIRED' => array(
				'txt' => '<strong>WP File-Tree requires you to set a base direcotry.</strong> ',
				'layout' => array()
			),
			'REQUIREMENTS_FAILED' => array(
				'txt' => '<strong>WP File-Tree Requirements Failed</strong><br />'
						. sprintf('Plugin requires PHP %1$s+ and WordPress %2$s+ to work correctly!', WPFileTree::$requirements['php'], WPFileTree::$requirements['wp']),
				'layout' => array()
			),
			'NO_MCRYPT_MODULE' => array(
				'txt' => '<strong>WP File-Tree Security Notice</strong><br />
						Plugin is working, but requires the PHP module <a href="http://mcrypt.sourceforge.net/" target="_blank">Mcrypt</a> for its functionality to be more secure.',
				'layout' => array()
			),
			'BASE_DIR_RESET' => array(
				'txt' => 'Your base directory has been reset to the wordpress root.',
				'layout' => array('color' => 'aa0') 
			),
			'BASE_DIR_SAVED' => array(
				'txt' => 'Your base directory has been saved. Make sure that it is valid!',
				'layout' => array('color' => '2d2') 
			),
			'BASE_DIR_VALID' => array(
				'txt' => 'This is a valid directory.',
				'layout' => array('color' => '2d2') 
			),
			'BASE_DIR_IS_NOT_READABLE' => array(
				'txt' => 'This directory is not readable!',
				'layout' => array('color' => 'd22')
			),
			'BASE_DIR_IS_NOT_DIR' => array(
				'txt' => 'This is not a directory!',
				'layout' => array('color' => 'd22') 
			)
		);
	}
	
	/*
	Singleton instance.
	Set as protected to allow extension of the class. To extend simply override the {@link getInstance()}
	var Messages
	*/
	protected static $_instance;
	
	/*
	Singleton instance.
	return Messages
	*/
	public static function getInstance() {
		if (null == self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/*
	Produces neccessary layout to display warning
	param  string $message Warning message to wrap in layout
	return string
	*/
	public function warning($message) {
		return '<div id="wp_file_tree-warning" class="updated fade"><p>' . $message . '</p></div>';
	}
	
	/*
	Now simply an alias for warning()
	param  string $message Warning message to wrap in layout
	return string
	*/
	public function info($message) {
		return $this->warning($message);
	}
	
	
	/*
	Returns pre-defined message
	param  string $tag What message to return
	param  string $key (Optional) Allows to provide specific key to return
	return array|string
	*/
	public function getMessage($tag, $key = null) {
		if (isset($this->messages[$tag])) {
			if (null != $key) {
				if (isset($this->messages[$tag][$key])) {
					return $this->messages[$tag][$key];
				} else {
					return '';
				}
			} else {
				return $this->messages[$tag];    
			}
			
		} else {
			return array();
		}
	}
	
	/*
	Returns messages identified by tag
	param string $tag Message index/tag
	return string
	*/
	public function infoMessage($tag) {
		if (isset($this->messages[$tag])) {
			return '<p style="margin: 0;padding: .5em; color: #' 
				  . $this->messages[$tag]['layout']['color'] . '; font-weight: bold;">' 
				  . $this->messages[$tag]['txt'] . '</p>';
		} else {
			return '';
		}
	}
	
	/*
	Echoes base dorectory required warning
	Just to make sure that user has set a base directory
	return void
	*/
	public function warningBaseDirMissing() {
		echo $this->warning($this->messages['BASE_DIR_REQUIRED']['txt']);
	}
	
	/*
	Echoes failed requirements warning
	return void
	*/
	public function warningRequirementsFailed() {
		echo $this->warning($this->messages['REQUIREMENTS_FAILED']['txt']);
	}
	
	/*
	Echoes no mcrypt module warning
	return void
	*/
	public function warningNoMcryptModule() {
		echo $this->warning($this->messages['NO_MCRYPT_MODULE']['txt']);
	}
}
?>