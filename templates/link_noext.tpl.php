<?php
$this->stripext = array_shift(explode('.', $this->basename));
?>
<a href="<?php echo $this->uri; ?>" title="<?php echo $this->stripext; ?>"><?php echo $this->stripext; ?></a>