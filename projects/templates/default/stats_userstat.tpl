<!-- $Id$ -->

{app_header}

<p><b>&nbsp;&nbsp;&nbsp;{lang_action}<b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<form method="POST" action="{actionurl}">
<table width="75%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td>{lang_lid}:</td>
		<td>{lid}</td>
	</tr>
	<tr>
		<td>{lang_firstname}:</td>
		<td>{firstname}</td>
	</tr>
	<tr>
		<td>{lang_lastname}:</td>
		<td>{lastname}</td>
	</tr>
	<tr>
		<td>{lang_start_date}</td>
		<td>{start_date_select}</td>
	</tr>
	<tr>
		<td>{lang_end_date}:</td>
		<td>{end_date_select}</td>
	</tr>
	<tr>
		<td>{lang_billedonly}:</td>
		<td><input type="checkbox" name="billed" value="billed" {billed}></td>
	</tr>
</table>
<table width="75%" border="0" cellspacing="2" cellpadding="2">
	<tr valign="bottom">
		<td height="50">
			<input type="submit" name="submit" value="{lang_calculate}">
		</td>
	</tr>
</table>
</form>
<table width="85%" border="0" cellspacing="2" cellpadding="2">
	<tr bgcolor="{th_bg}">
		<td width="10%" bgcolor="{th_bg}">{lang_project}</td>
		<td width="10%" bgcolor="{th_bg}">{lang_activity}</td>
		<td width="10%" bgcolor="{th_bg}" align="right">{lang_hours}</td>
	</tr>

<!-- BEGIN user_stat -->

	<tr bgcolor="{tr_color}">
		<td>{e_project}</td>
		<td>{e_activity}</td>
		<td align="right">{e_hours}</td>
	</tr>

<!-- END user_stat -->

</table>
</center>
