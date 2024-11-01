<?php
$this->rel = 'iframe';
$this->title = $this->basename . ' :: ' . 'URI: ' . $this->uri . ' :: fullscreen: true';
switch($this->mimetype) {
	case 'image/jpeg' :
	case 'image/png' :
	case 'image/gif' :
		$this->rel = 'gallery[myset]';
		$this->title = $this->basename;
	break;
}
?>
<a class="lightview" rel="<?php echo $this->rel; ?>" href="<?php echo $this->uri; ?>" title="<?php echo $this->title; ?>"><?php echo $this->basename; ?></a>