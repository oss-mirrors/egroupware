<!-- $Id$ -->

{app_header}

<table border="0" width="100%" cellpadding="2" cellspacing="2">
	<form method="POST" action="{action_url}">
	<input type="hidden" name="project_id" value="{project_id}">
	<tr height="50">
		<td width="20%">&nbsp;</td>
		<td>{lang_start_date}:&nbsp;{sdate_select}</td>
		<td>{lang_end_date}:&nbsp;{edate_select}</td>
		<td align="right"><input type="submit" name="show" value="{lang_show_chart}"></td>
		<td width="20%">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="5" align="center"><img src="{pix_src}" border="0"></td>
	</tr>
	</form>
</table>
