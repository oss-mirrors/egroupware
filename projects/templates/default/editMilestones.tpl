<!-- $Id$ -->
<!-- BEGIN main -->
<form method="POST" action="{actionURL}">
<table border="0" cellspacing="0" cellpadding="2" width="100%">
	<tr class="th" valign="bottom">
		<td align="left" ><input type="text" name="values[title]" size="50" value="{title}"></td>
		<td align="right">{end_date_select}</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<textarea name="values[description]" cols="50" rows="5">{description}</textarea>
		</td>
	</tr>
	<tr valign="bottom">
		<td align="left" colspan="1">
			<input type="submit" name="done" value="{lang_done}" onclick="window.close();">
			<INPUT type="hidden" name="old_edate" value="{old_edate}">
		</td>
		<td align="right" colspan="1"><input type="submit" name="save" value="{lang_save}"></td>
	</tr>
</table>
</form>
<!-- END main -->