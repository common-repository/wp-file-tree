<?php
list ($this->width, $this->height) = getimagesize($this->uri);
?>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" id="file_<?php echo $this->index; ?>_object" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab" align="middle" width="<?php echo $this->width; ?>" height="<?php echo $this->height; ?>">
	<param name="movie" value="<?php echo $this->uri; ?>" />
    <param name="allowFullScreen" value="true" />
    <param name="allowScriptAccess" value="always" />
    <embed src="<?php echo $this->uri; ?>" quality="high" name="file_<?php echo $this->index; ?>_object" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" align="middle" width="<?php echo $this->width; ?>" height="<?php echo $this->height; ?>">
</object>