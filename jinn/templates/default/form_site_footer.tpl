<!-- BEGIN header -->
<form method="post" name="frm" action="{form_action}" enctype="multipart/form-data" {form_attributes}>
{where_key_form}
{where_value_form}
<table align="center" cellspacing="2" cellpadding="2" width="100%">
<!-- END header -->


<!-- BEGIN footer -->
</tr>
</table>
<table width="100%" align="center">
	<tr>
        <td align="center">
		<input type="submit" name="continue" value="{save_and_continue_button}">
		<input type="submit" name="add" value="{save_button}">
		{delete}
		{cancel}
		{extra_buttons}
	</tr>
</table>
</form>
<br/><div style="width:100%;background-color:grey;height:1px;"></div><br/>
<!-- END footer -->


<!-- BEGIN object_buttons -->
<input type="button" value="{lang_add_object}" onclick="document.location.href='{link_add_object}'">
<input type="button" value="{lang_auto_add_object}" onclick="document.location.href='{link_auto_add_object}'">
<!-- END object_buttons -->
