<center>
<table border="0" width="80%" cellpadding="2" cellspacing="2">
<form method="POST" action="{actionurl}">
	<tr>
		<td colspan="2" align="center" bgcolor="{th_bg}"><b>{lang_action}</b></td>
	</tr>
	<tr>
		<td colspan="2" height="30">&nbsp;</td>
	</tr>
	<tr>
		<td>{lang_users_list}:</td>
		<td><select name="users[]" multiple>{users_list}</select></td>
	</tr>
	<tr>
		<td>{lang_groups_list}:</td>
		<td><select name="groups[]" multiple>{groups_list}</select></td>
	</tr>
    <tr>
		<td>
			<input type="submit" name="submit" value="{lang_save}">
		</td>
	</tr>
</form>
</table>
</center>
