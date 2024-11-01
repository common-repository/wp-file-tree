<?php
if (!class_exists('GeSHi')) {
	require_once('Classes/geshi.php');
}
$this->geshi = new GeSHi($this->contents, 'php');
$this->geshi->set_header_type(GESHI_HEADER_NONE);
$this->contents = $this->geshi->parse_code();
?>
<div style="border: 1px solid #CCCCCC; padding: 10px; margin-top: 10px; margin-bottom: 10px;">
    <code><?php echo $this->contents; ?></code>
</div>