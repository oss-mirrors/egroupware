<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<form method="POST" name="activity_form" action="{actionurl}">
{pref_message}<br>{message}
<table width="75%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td>{lang_choose}</td>
		<td>{choose}</td>
	</tr>
	<tr>
		<td>{lang_act_number}:</td>
		<td><input type="text" name="values[number]" value="{num}"></td>
	</tr>
	<tr>
		<td>{lang_descr}:</td>
		<td colspan="2"><textarea name="values[descr]" rows=4 cols=50 wrap="VIRTUAL">{descr}</textarea></td>
	</tr>
	<tr>
		<td>{lang_category}:</td>
		<td><select name="new_cat"><option value="">{lang_none}</option>{cats_list}</select></font></td>
	</tr>
	<tr>
		<td>{lang_remarkreq}:</td>
		<td><select name="values[remarkreq]">{remarkreq_list}</select></td>
	</tr>
	<tr>
		<td>{currency}&nbsp;{lang_billperae}:</td>
		<td><input type="text" name="values[billperae]" value="{billperae}"></td>
	</tr>
	<tr>
		<td>{lang_minperae}:</td>
		<td><input type="text" name="values[minperae]" value="{minperae}"></td>
	</tr>
</table>
         
<!-- BEGIN add -->
         
<table width="50%" border="0" cellspacing="2" cellpadding="2">
	<tr valign="bottom">
		<td height="50" align="center"><input type="submit" name="submit" value="{lang_save}"></td>
		<td height="50" align="center"><input type="reset" name="reset" value="{lang_reset}"></form></td>
		<td height="50" align="center">
			<form method="POST" action="{done_url}">
			<input type="submit" name="done" value="{lang_done}"></form></td>
	</tr>
</table>
</center>

<!-- END add -->

<!-- BEGIN edit -->

<table width="50%" border="0" cellspacing="2" cellpadding="2">
	<tr valign="bottom">
		<td height="50" align="center">
			<input type="submit" name="submit" value="{lang_save}"></form></td>
		<td height="50" align="center">
			<form method="POST" action="{deleteurl}">
			<input type="submit" name="delete" value="{lang_delete}"></form></td>
		<td height="50" align="center">
			<form method="POST" action="{done_url}">
			<input type="submit" name="done" value="{lang_done}"></form></td>
	</tr>
</table>
</center>

<!-- END edit -->
