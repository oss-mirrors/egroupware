<!-- BEGIN header -->
<form method="post" name="frm" action="{form_action}" enctype="multipart/form-data" {form_attributes}>
{where_string_form}
<table align="" cellspacing="2" cellpadding="2" style="background-color:#ffffff;border:solid 1px #cccccc;">
<!-- END header -->


<!-- BEGIN rows -->
<tr><td bgcolor="{row_color}" valign="top">{fieldname}</td>
<td bgcolor="{row_color}">{input}</td></tr>
<!-- END rows -->

<!-- BEGIN back_button -->
	<input type="button" onClick="{back_onclick}" value="{lang_back}">
<!-- END back_button -->

<!-- BEGIN footer -->
	</tr>
	<tr>
	<td colspan="2" bgcolor="{row_color}">
	<input type="button" onClick="{edit_onclick}" value="{lang_edit}">
	{extra_back_button}
	<!--<input type="submit" name="delete" value="{delete}">-->

	</td></tr>
	<tr><td colspan="2" >
	<table align="right" style="background-color:#ffffff">
	<tr>
	<td>	
	</td>
	</tr>
	</table>
	
	</td></tr>

</table>
</form>
<!-- END footer -->

