<!-- $Id$ -->

<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<form method="POST" name="admin_form" action="{actionurl}">
{message}
<table width="85%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td>{lang_b_create}</td>
		<td>{b_create}</td>
	</tr>
	<tr>
		<td>{lang_b_intval}:</td>
		<td><select name="values[b_intval]"><option value="">{lang_select_b_intval}</option>{intval_list}</select></td>
	</tr>
	<tr>
		<td><b>{lang_b_data}</b></td>
	</tr>
	<tr>
		<td>{lang_b_sql}:</td>
		<td>{b_sql}</td>
	</tr>
	<tr>
		<td>{lang_b_ldap}:</td>
		<td>{b_ldap}</td>
	</tr>
	<tr>
		<td>{lang_b_email}:</td>
		<td>{b_email}</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><b>{lang_r_config}</b></td>
	</tr>
	<tr>
		<td>{lang_r_save}</td>
		<td>{r_save}</td>
	</tr>
	<tr>
		<td>{lang_r_host}:</td>
		<td>{r_host}</td>
	</tr>
	<tr>
		<td>{lang_r_path}:</td>
		<td><input type="text" name="values[r_path]" value="{r_path}"></td>
	</tr>
	<tr>
		<td>{lang_r_ip}:</td>
		<td><input type="text" name="values[r_ip]" value="{r_ip}"></td>
	</tr>
	<tr>
		<td>{lang_r_user}:</td>
		<td><input type="text" name="values[r_user]" value="{r_user}"></td>
	</tr>
	<tr>
		<td>{lang_r_pwd}:</td>
		<td><input type="text" name="values[r_pwd]" value="{r_pwd}"></td>
	</tr>
</table>

<table width="85%" border="0" cellspacing="2" cellpadding="2">
	<tr valign="bottom" align="left">
		<td height="50">
			<input type="submit" name="submit" value="{lang_save}"></form></td>
	</tr>
</table>
</center>
