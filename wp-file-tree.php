<?php
/*
Plugin Name: WP File-Tree
Plugin URI: http://wordpress.org/extend/plugins/wp-file-tree/
Description: Using shortcode and templates, post files from a secure web directory with mod_rewrite enabled uri links to view and download.
Version: 1.1.2
Author: jimisaacs
Author URI: http://ji.dd.jimisaacs.com/
*/

function wp_file_tree_activate() {
	/* add all options with defaults into database */
	update_option('wp_file_tree_base_dir', ABSPATH);
	update_option('wp_file_tree_uri_type', '');
	update_option('wp_file_tree_link_base', 'wp-file-tree-link');
	update_option('wp_file_tree_download_base', 'wp-file-tree-download');
}

function wp_file_tree_deactivate(){
	/* empty the mod_rewrite rules from the .htaccess file */
	$filename = ABSPATH . '.htaccess';
	if(file_exists($filename) && is_readable($filename)) {
		$htaccess = file_get_contents( ABSPATH . '.htaccess');
		$pattern = "(.*)#BEGIN WP File-Tree(.+)#END WP File-Tree(.*)";
		$replacement = "\\1#BEGIN WP File-Tree
#END WP File-Tree\\3";
		$htaccess = ereg_replace($pattern, $replacement, $htaccess);
		file_put_contents($filename, $htaccess);
	}
	/* delete all options from database */
	delete_option('wp_file_tree_base_dir');
	delete_option('wp_file_tree_uri_type');
	delete_option('wp_file_tree_link_base');
	delete_option('wp_file_tree_download_base');
}

register_activation_hook(__FILE__, 'wp_file_tree_activate');
register_deactivation_hook(__FILE__, 'wp_file_tree_deactivate');


/* Make sure that plugin DIR is in include_path */
set_include_path(realpath(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());

/* class WPFileTree */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'WPFileTree.php';

/* Object instantination is enough, hooks are registered in class */
$wp_file_tree = new WPFileTree();
?>