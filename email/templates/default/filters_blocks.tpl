<!-- begin file filters_blocks.tpl -->

&nbsp; <!-- === block seperator == --> &nbsp; 

<!-- BEGIN B_account_and_or_ignore -->
<select name="{andor_select_name}">
	<option value="ignore_me" selected>&lt;{lang_ignore_me1}&gt;</option>
	<option value="or">{lang_or}</option>
	<option value="and">{lang_and}</option>
</select>
<!-- END B_account_and_or_ignore -->

&nbsp; <!-- === block seperator == --> &nbsp; 
	
<!-- BEGIN B_action_no_ignore -->
<select name="{actionbox_name}">
	<option value="keep">{lang_keep}</option>
	<option value="discard">{lang_discard}</option>
	<option value="reject">{lang_reject}</option>
	<option value="redirect">{lang_redirect}</option>
	<option value="fileinto" selected>{lang_fileinto}</option>
</select>

<!-- END B_action_no_ignore -->

&nbsp; <!-- === block seperator == --> &nbsp; 
	
<!-- BEGIN B_action_with_ignore_me -->
<select name="{actionbox_name}">
	<option value="ignore_me" selected>&lt;{lang_ignore_me2}&gt;</option>
	<option value="keep">{lang_keep}</option>
	<option value="discard">{lang_discard}</option>
	<option value="reject">{lang_reject}</option>
	<option value="redirect">{lang_redirect}</option>
	<option value="fileinto">{lang_fileinto}</option>
</select>

<!-- END B_action_with_ignore_me -->

&nbsp; <!-- === block seperator == --> &nbsp; 

<!-- end file filters_blocks.tpl -->
