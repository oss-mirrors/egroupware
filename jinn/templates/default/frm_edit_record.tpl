<!-- BEGIN form_header -->
   <script language="javascript" type="text/javascript">
function img_popup(img,pop_width,pop_height,attr)
{
   options="width="+pop_width+",height="+pop_height+",location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no";
   parent.window.open("{popuplink}&path="+img+"&attr="+attr, "pop", options);
}
</script>


<form method="post" name="frm" action="{form_action}" enctype="multipart/form-data" {form_attributes}>
{where_string_form}
<table align="" cellspacing="2" cellpadding="2" style="background-color:#ffffff;border:solid 1px #cccccc;">
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
	<tr><td colspan="2" bgcolor="{row_color}">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="{row_color}">
	<table align="right">
	<tr>
	<td><input type="submit" name="continue" value="{add_edit_button_continue}"></td>
	<td><input type="submit" name="save" value="{add_edit_button}"></td>
	<td><input type="reset" value="{reset_form}"></td>
	<td><input type="submit" name="delete" value="{delete}"></td>
	<td>{cancel}</td>
	</tr>
	</table>
	
	</td></tr>

</table>
<table align="center" ><tr><td>{extra_buttons}</td></tr></table>
<!--<table width="80%" align="center">
	<tr>
		<td><input type="submit" name="continue" value="{add_edit_button_continue}"></td>
		<td><input type="submit" name="save" value="{add_edit_button}"></td>
		<td><input type="submit" name="delete" value="{delete}"></td>
		<td>{cancel}</td>
	</tr>
</table>-->
</form>
<!-- END form_footer -->

