<?php
/*
name: WPFileTree
type: class 
package: wp-file-tree

description: Plugin Main Class
adds all plugin functionality using the delegator pattern
and singletons

author:			jimisaacs
author uri:		http://ji.dd.jimisaacs.com
*/


/* class Delegator */
require_once 'WPFileTree/Patterns/Delegator.php';

/* static class JIMcrypt */
require_once('Classes/JIMcrypt.php');


class WPFileTree {

	/*
	Uri where plugin is located
	var String
	*/
	protected $pluginUri;
	
	/*
	Base dir plugin option - File path
	var String
	*/
	protected $base_dir;
	
	/*
	var boolean
	*/
	protected $mcrypt_installed;
    
	/*
	List of WP File-Tree requirements
	var Array
	*/
	public static $requirements = array(
		'php' => '5.1',
		'wp' => '2.5.1'
	);
    
	
	/*
	Initializes and runs the plugin functionality
	return void
	*/
    public function __construct() {
	
		/* set variables */
        $this->pluginUri = get_option('siteurl') . '/wp-content/plugins/wp-file-tree/';
		$this->mcrypt_installed = function_exists('mcrypt_module_open');
		$this->base_dir = get_option('wp_file_tree_base_dir');
		
		/* bind admin functionality to admin pages only */
		if(defined('WP_ADMIN')) {
			/* Run plugin */
			$this->initAdmin();
		} else {
			/* Run plugin */
			$this->initClient();
		}
    }
    
	/*
	Catch-all methods, delegator pattern is used to invoke various handlers
	param  String $method	-------	Name of called method
	param  Array  $args	-----------	List of arguments
	return mixed
	*/
    public function __call($method, $args = array()) {
        return Delegator::getInstance()->__call($method, $args);
    }
	
	/*
	Initialize the plugin for Admin
	return void
	*/
	public function initAdmin() {
		/* Make sure that minimum requirements are met to initiate admin notices */
		$this->checkRequirements(); 
		/* Register Admin handlers (that would get calls via __call()) */
		$this->registerAdminHandlers();
		/* Initializes all required WP plugin hooks */
		add_action('init', array($this, 'registerAdminHooks'));
	}
	
	/*
	Initializes the plugin for Client
	return void
	*/
	public function initClient() {
		/* Register Client handlers (that would get calls via __call()) */
		$this->registerClientHandlers();
		/* Initializes all required WP plugin hooks */
		add_action('init', array($this, 'registerClientHooks'));
	}
	
	/*
	Run admin notices for the requirements of the plugin
	return void
	*/
	public function checkRequirements() {
	
		global $wp_version;
		
		/* check php version */
		if (!version_compare(phpversion(), WPFileTree::$requirements['php'], '>=')) {
			add_action('admin_notices', array($this, 'warningRequirementsFailed'));
		}
		/* check wp version */
		if (!version_compare($wp_version, WPFileTree::$requirements['wp'], '>=')) {
			add_action('admin_notices', array($this, 'warningRequirementsFailed'));
		}
		/* make sure that user warned about necessity of base_dir */
		if (!$this->base_dir) {
			add_action('admin_notices', array($this, 'warningBaseDirMissing'));
		}
		/* check if mcrypt module is installed */
		if (!$this->mcrypt_installed) {
			add_action('admin_notices', array($this, 'warningNoMcryptModule'));
		}
	}
    
	/*
	Registers Admin command-chain of handlers that would be called via proxy of current class
	return void
	*/
    public function registerAdminHandlers() {
	
        $delegator = Delegator::getInstance();
		
		require_once 'WPFileTree/Messages.php';
        $delegator->addTarget(Messages::getInstance());
        
        require_once 'WPFileTree/Pages/GeneralConfig.php';
        $delegator->addTarget(GeneralConfig::getInstance());
    }
	
	/*
	Registers Client command-chain of handlers that would be called via proxy of current class
	return void
	*/
    public function registerClientHandlers() {
	
        $delegator = Delegator::getInstance();
        
        require_once 'WPFileTree/Shortcode.php';
        $delegator->addTarget(Shortcode::getInstance());
    }
  
	/*
	Initializes Admin required WP actions and filters
	return void
	*/
	public function registerAdminHooks() {
		
		// configuration page(s)
		add_action('admin_menu', array($this, 'addConfigPage')); 
			
		// make sure that custom headers are loaded in admin part of site
		add_action('admin_print_scripts', array($this, 'bindAdminHeaders'));
	}
	
	/*
	Initializes Client required WP actions and filters
	return void
	*/
	public function registerClientHooks() {
	
		/* Check and do a link action for wp-file-tree parameter. */
		if(isset($_REQUEST['wp-file-tree'])) {
			require_once('resource_header.php');
		}
				
		/* 
		replace the attributes and content of the [file] shortcode tags to encoded values
		This is so the shortcode parser can bypass ALL 3rd party plugin and wordpress autoformatting
		The priority for this filter should make it run first, if not the change the priority!
		*/
		add_filter('the_content', array($this, 'content_shortcode_encode'), -1000);
					
		/* make sure that custom headers are loaded in client part of site */
		add_action('wp_print_scripts', array($this, 'bindClientHeaders'));
	}
    
	/*
	WordPress Hook - add_action - admin_print_scripts
	All Admin head hooks should go here
	return void
	*/
	public function bindAdminHeaders() {
		echo '<!-- WP File-Tree -->' . "\n"
		   . '<link type="text/css" rel="stylesheet" href="'. $this->pluginUri . 'css/layout.css" />';
		wp_print_scripts('WPFileTree'); // should be used instead of wp_enqueue_script because of below init block
		echo "\n<!-- /WP File-Tree -->\n";
	}
    
	/*
	WordPress Hook - add_action - wp_print_scripts
	All Client head hooks should go here
	return void
	*/
	public function bindClientHeaders() {
		wp_enqueue_script('WPFileTree');
	}
    
	/*
	WordPress Hook - add_action - admin_menu
	Renders and returns main configuration page
	return String
	*/
	public function addConfigPage() {
		if ( function_exists('add_submenu_page') ){
			add_submenu_page('options-general.php', 'WP File-Tree', 'WP File-Tree', 'manage_options', 'wp_file_tree-config', array($this, 'run_GeneralConfig'));	
		}
	}
}
?>