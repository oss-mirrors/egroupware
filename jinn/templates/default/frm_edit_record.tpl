<!-- BEGIN form_header -->
<form method="post" name="frm" action="{form_action}" enctype="multipart/form-data" {form_attributes}>
{where_string_form}
<table align="center" cellspacing="2" cellpadding="2" width="80%">
<!-- END form_header -->



<!-- BEGIN js -->
<script language="JavaScript">
<!--

function onSubmitForm() {

{submit_script}

return true;
}

//-->
</script>
<!-- END js -->



<!-- BEGIN rows -->
<tr><td bgcolor="{row_color}" valign="top">{fieldname}</td>
<td bgcolor="{row_color}">{input}</td></tr>
<!-- END rows -->



<!-- BEGIN form_footer -->
	</tr>
</table>
<table align="center" width="80%"><tr><td>{extra_buttons}</td></tr></table>
<table width="80%" align="center">
	<tr>
		<td><input type="submit" name="continue" value="{add_edit_button_continue}"></td>
		<td><input type="submit" name="save" value="{add_edit_button}"></td>
		<td><input type="reset" value="{reset_form}"></td>
		<td><input type="submit" name="delete" value="{delete}"></td>
		<td>{cancel}</td>
	</tr>
</table>
</form>
<!-- END form_footer -->

