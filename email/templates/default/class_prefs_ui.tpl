<!-- begin class_prefs_ui.tpl -->
{pref_errors}
<p>
  <b>{page_title}</b>
  <hr>
</p>

<form method="POST" action="{form_action}">
<table border="0" align="center" cellspacing="1" cellpadding="1" width="75%">

{prefs_ui_rows}

<tr>
	<td colspan="2" align="center">
		<input type="submit" name="{btn_submit_name}" value="{btn_submit_value}">
	</td>
</tr>


</table>
</form>
<!-- end class_prefs_ui.tpl -->
