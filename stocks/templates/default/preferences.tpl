<!-- $Id$ -->
<center>
<table width="60%" border="0" cellspacing="2" cellpadding="2">
	<form method="POST" action="{actionurl}">
	<tr bgcolor="{th_bg}">
		<td colspan="2"><b>{lang_action}</b></td>
	</tr>
	<tr bgcolor="{tr_color1}">
		<td>{lang_display}:</td>
		<td align="center">{mainscreen}</td>
	</tr>
	<tr bgcolor="{tr_color2}">
		<td>{lang_def_country}:</td>
		<td align="center"><select name="prefs[country]">{country_list}</select></td>
	</tr>
	<tr valign="bottom">
		<td><input type="submit" name="prefs[submit]" value="{lang_save}"></form></td>
		<form method="POST" action="{doneurl}">
		<td><input type="submit" name="done" value="{lang_done}"></form></td>
	</tr>
</table>
</center>
