<!-- $Id$ -->
<center>
<form method="POST" name="app_form" action="{action_url}">
<table border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td valign="top">{lang_select_columns}:</td>
		<td><select name="cols[]" multiple>{column_select}</select></td>
	</tr>
	<tr valign="bottom" height="50">
		<td>
			<input type="submit" name="save" value="{lang_save}">
		</td>
		<td align="right">
			<input type="submit" name="done" value="{lang_done}"></td>
	</tr>
</table>
</form>
</center>
