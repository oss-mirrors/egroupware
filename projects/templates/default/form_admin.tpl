<center>
<table border="0" width="80%" cellpadding="2" cellspacing="2">
<form method="POST" action="{action_url}">
	<tr>
		<td valign="top">{lang_users_list}:</td>
		<td><select name="users[]" multiple>{users_list}</select></td>
	</tr>
	<tr>
		<td valign="top">{lang_groups_list}:</td>
		<td><select name="groups[]" multiple>{groups_list}</select></td>
	</tr>
    <tr height="50" valign="bottom">
		<td><input type="submit" name="save" value="{lang_save}"></td>
		<td align="right"><input type="submit" name="cancel" value="{lang_cancel}"></td>
	</tr>
</form>
</table>
</center>
