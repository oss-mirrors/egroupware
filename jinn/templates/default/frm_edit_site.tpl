<!-- BEGIN header -->
<form method="post" name="frm" action="{form_action}" enctype="multipart/form-data" {form_attributes}>
{where_key_form}
{where_value_form}
<table align="center" cellspacing="2" cellpadding="2" width="100%">
<!-- END header -->

<!-- BEGIN rows -->
<tr><td bgcolor={row_color} valign="top">{fieldname}</td><td bgcolor={row_color}>{input}</td></tr>
<!-- END rows -->

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
<!-- END footer -->
