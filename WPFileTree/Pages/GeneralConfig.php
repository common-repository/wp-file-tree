<?php
/*
name: GeneralConfig
type: class extends Pages 
package: WPFileTree/Pages

description: The page that is added to the WordPress admin
Simply for plugin configuration

author:			jimisaacs
author uri:		http://ji.dd.jimisaacs.com
*/


/* class Page */
require_once 'WPFileTree/Page.php';


class GeneralConfig extends Page {
	/*
	Singleton instance
	var GeneralConfig
	*/
    protected static $_instance;
    
	/*
	Returns singleton instance
	return GeneralConfig
	*/
    public static function getInstance() {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
	/*
	WordPress Hook - add_submenu_page - options-general.php
	Dipslays page
	return void
	*/
    public function run_GeneralConfig() {
		
		$siteurl = get_option('siteurl');
		$base_dir = get_option('wp_file_tree_base_dir');
		$uri_type = get_option('wp_file_tree_uri_type');
		$link_base = get_option('wp_file_tree_link_base');
		$download_base = get_option('wp_file_tree_download_base');

        $ms = array();
        
		if (isset($_POST['submit_1']) || isset($_POST['reset'])) { // process options
			$base_dir = $_POST['base_dir'];
			if (empty($_POST['base_dir']) || isset($_POST['reset'])) {
				$ms[] = 'BASE_DIR_RESET';
				$base_dir = ABSPATH;
			} else {
				$ms[] = 'BASE_DIR_SAVED';
			}
			update_option('wp_file_tree_base_dir', $base_dir);
		}
		if (isset($_POST['submit_2'])) { // process options
		
			$link_base = (empty($_POST['link_base'])) ? 'wp-file-tree-link' : $_POST['link_base'];
			$download_base = (empty($_POST['download_base'])) ? 'wp-file-tree-download' : $_POST['download_base'];
			update_option('wp_file_tree_link_base', $link_base);
			update_option('wp_file_tree_download_base', $download_base);
			
			switch($_POST['uri_type']) {
				case '' :
					$filename = ABSPATH . '.htaccess';
					if(file_exists($filename) && is_readable($filename)) {
						$htaccess = file_get_contents( ABSPATH . '.htaccess');
						$pattern = "(.*)#BEGIN WP File-Tree(.+)#END WP File-Tree(.*)";
						$replacement = "\\1#BEGIN WP File-Tree
#END WP File-Tree\\3";
						$htaccess = ereg_replace($pattern, $replacement, $htaccess);
						file_put_contents($filename, $htaccess);
					}
					update_option('wp_file_tree_uri_type', $_POST['uri_type']);		
				break;
				case 'mod_rewrite' :
					$filename = ABSPATH . '.htaccess';
$content = '
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^' . $link_base . '/(.+)$ index.php?wp-file-tree=$1 [QSA]
RewriteRule ^' . $download_base . '/(.+)$ index.php?wp-file-tree=$1&download [QSA]
</IfModule>
';
					if(file_exists($filename) && is_readable($filename)) {
						$htaccess = file_get_contents( ABSPATH . '.htaccess');
						if(ereg('.*#BEGIN WP File-Tree(.+)#END WP File-Tree.*', $htaccess)) {
							//echo $matches[0];
							$pattern = "(.*)#BEGIN WP File-Tree(.+)#END WP File-Tree(.*)";
							$replacement = "\\1#BEGIN WP File-Tree" . $content . "#END WP File-Tree\\3";
							$htaccess = ereg_replace($pattern, $replacement, $htaccess);
						} else {
							$htaccess = '#BEGIN WP File-Tree'.$content.'#END WP File-Tree
'.$htaccess;
						}
						file_put_contents($filename, $htaccess);
						update_option('wp_file_tree_uri_type', $_POST['uri_type']);
					} else {
						$htaccess = '#BEGIN WP File-Tree'.$content.'
#END WP File-Tree';
						file_put_contents($filename, $htaccess);
					}
				break;
			}
			$uri_type = $_POST['uri_type'];
		}
		if(is_dir($base_dir) && is_readable($base_dir)) {
			$notice = 'BASE_DIR_VALID';
		} else if (is_dir($base_dir)) {
			$notice = 'BASE_DIR_IS_NOT_READABLE';
		} else {
			$notice = 'BASE_DIR_IS_NOT_DIR';
		}
		?>
        <div class="wp_file_tree">
			<?php if (!empty($_POST)) { ?>
            <div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>
            <?php } ?>
            <div class="wrap">
                <h2>WP File-Tree Configuration</h2>
                <div class="wide">
                    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="wp_file_tree-conf1">
                    	<h3>Base Directory</h3>
                        <p>This directory is the base directory that all other file paths in WP File-Tree uses.<br />
                        To put simply, for this plugin, it is your root or '/' directory.<br />
                        <br />
                        It may be abolute, or relative to your wordpress root directory.<br />
                        <br />
                        To be safe, this directory can and should not be in your wordpress directory, actually not even in your server's htdocs directory!<br />
                        One of the points of this plugin is to effectively allow easy posting of secure files through WordPress.</p>
                        <table class="form-table">
                            <tr>
                                <th>
                                <?php foreach ( $ms as $m ) {
									echo Messages::getInstance()->infoMessage($m);
								} ?>
                                <input id="base_dir" name="base_dir" type="text" size="50" value="<?php echo $base_dir; ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /><?php echo Messages::getInstance()->infoMessage($notice); ?></th>
                            </tr>
                        </table>
                        <p class="submit"><input type="submit" name="reset" value="Reset to WordPress root &raquo;" /> <input type="submit" name="submit_1" value="Save Base Directory &raquo" /></p>
                    </form>
                    <form action="#conf2" method="post" id="wp_file_tree-conf2">
                       <h3>Permalinks</h3><a name="conf2"></a>
                        <table class="form-table">
                            <tr>
                                <th><label><input name="uri_type" type="radio" value="" class="tog" <?php if($uri_type == '') { echo 'checked="checked" '; } ?>/> default</label></th>
                                <td><code><?php echo $siteurl; ?>/?wp-file-tree=path/to/file</code></td>
                            </tr>
                            <tr>
                                <th><label><input name="uri_type" type="radio" value="mod_rewrite" class="tog" <?php if($uri_type == 'mod_rewrite') { echo 'checked="checked" '; } ?>/> mod_rewrite</label></th>
                                <td><code><?php echo $siteurl; ?>/<?php echo $link_base; ?>/path/to/file</code></td>
                            </tr>
                            <tr>
                                <th><label for="link_base">link base (slug)</label></th>
                                <td><input id="link_base" name="link_base" type="text" size="50" value="<?php echo $link_base; ?>" /></td>
                            </tr>
                            <tr>
                                <th><label for="download_base">download base (slug)</label></th>
                                <td><input id="download_base" name="download_base" type="text" size="50" value="<?php echo $download_base; ?>" /></td>
                            </tr>
                        </table>
                        <p class="submit"><input type="submit" name="submit_2" value="Save Link Type &raquo" /></p><br />
                    </form>
                </div>
                <?php if (!empty($_POST)) { ?>
                <div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>
                <?php } ?>
            </div>
        </div>
        <?php
    }
}
?>