<?php
$this->stripext = array_shift(explode('.', $this->basename));
?>
<a href="<?php echo $this->download_uri; ?>" title="<?php echo $this->stripext; ?>"><?php echo $this->stripext; ?> &raquo; download</a>