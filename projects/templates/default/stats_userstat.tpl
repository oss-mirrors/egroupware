<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}<b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<form method="POST" name="projects_form" action="{actionurl}">
{hidden_vars}
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
		<td>{billedonly}:</td>
		<td><input type="checkbox" name="billed" value="billed" {billed}></td>
	</tr>
</table>
<table width="75%" border="0" cellspacing="2" cellpadding="2">
	<tr valign="bottom">
		<td height="50">
			<input type="submit" name="submit" value="{lang_calcb}">
		</td>
	</tr>
</table>
</form>
<table width="85%" border="0" cellspacing="2" cellpadding="2">
	<tr bgcolor="{th_bg}">
		<td width="10%" bgcolor="{th_bg}">{hd_project}</td>
		<td width="10%" bgcolor="{th_bg}">{hd_activity}</td>
		<td width="10%" bgcolor="{th_bg}" align="right">{hd_hours}</td>
	</tr>

<!-- BEGIN stat_list -->

	<tr bgcolor="{tr_color}">
		<td>{e_project}</td>
		<td>{e_activity}</td>
		<td align="right">{e_hours}</td>
	</tr>

<!-- END stat_list -->

</table>
</center>
