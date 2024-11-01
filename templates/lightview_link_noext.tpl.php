<?php
$this->stripext = array_shift(explode('.', $this->basename));
$this->rel = 'iframe';
$this->title = $this->stripext . ' :: ' . 'URI: ' . $this->uri . ' :: fullscreen: true';
switch($this->mimetype) {
	case 'image/jpeg' :
	case 'image/png' :
	case 'image/gif' :
		$this->rel = 'gallery[myset]';
		$this->title = $this->stripext;
	break;
}
?>
<a class="lightview" rel="<?php echo $this->rel; ?>" href="<?php echo $this->uri; ?>" title="<?php echo $this->title; ?>"><?php echo $this->stripext; ?></a>