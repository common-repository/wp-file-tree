<form style="display:none;" action="<?php echo $this->download_uri; ?>" class="wp_file_tree_download_form" name="file_<?php echo $this->index; ?>_form" method="post">
	<input type="hidden" name="key" value="<?php $this->get_post_key($this->download_uri); ?>">
    <input type="hidden" name="contents" value="<?php $this->get_compressed($this->contents); ?>">
</form>
<a href="javascript:void;" onClick="document.file_<?php echo $this->index; ?>_form.submit()" title="<?php echo $this->basename; ?>"><?php echo $this->basename; ?> &raquo; download</a>