<!-- $Id$ -->

{app_header}

<p><b>&nbsp;&nbsp;&nbsp;{lang_header}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<table border="0" width="98%" cellpadding="2" cellspacing="2">
	<tr colspan="9">
		<td colspan="9">
			<table border="0" width="100%">
				<tr>
				{left}
					<td align="center">{lang_showing}</td>
				{right}
				</tr>
			</table>
		</td>
	</tr>
	<tr colspan="9">
		<td width="25%" align="left">
			{action_list}
			<noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript></form></td>
		<td width="25%" align="center"><form method="POST" name="status" action="{status_action}">
			<select name="status" onChange="this.form.submit();">{status_list}</select><noscript>
			&nbsp;<input type="submit" name="submit" value="Submit"></noscript></form></td>
		<td width="25%" align="center"><form method="POST" name="filter" action="{filter_action}">{filter_list}</form></td>
		<td width="25%" align="right"><form method="POST" name="query" action="{search_action}">{search_list}</form></td>
	</tr>
</table>
<table border="0" width="98%" cellpadding="2" cellspacing="2">
	<tr bgcolor="{th_bg}">
		<td width="8%" bgcolor="{th_bg}">{sort_number}</td>
		<td width="20%" bgcolor="{th_bg}">{sort_title}</td>
		<td width="20%" bgcolor="{th_bg}">{sort_coordinator}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{sort_status}</td>
        <td width="20%" bgcolor="{th_bg}">{sort_action}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{sort_end_date}</td>
		<td width="5%" bgcolor="{th_bg}" align="center">{lang_action}</td>
		<td width="5%" bgcolor="{th_bg}" align="center">{lang_view}</td>
		<td width="5%" bgcolor="{th_bg}" align="center">{lang_edit}</td>
	</tr>

<!-- BEGIN projects_list -->

	<tr bgcolor="{tr_color}">
		<td>{number}</td>
		<td>{title}</td>
		<td>{coordinator}</td>
		<td align="center">{status}</td>
        <td>{td_action}</td>
		<td align="center">{end_date}</td>
		<td align="center"><a href="{action_entry}">{lang_action_entry}</a></td>
		<td align="center"><a href="{view}">{lang_view_entry}</a></td>
		<td align="center"><a href="{edit}">{lang_edit_entry}</a></td>
	</tr>

<!-- END projects_list -->

	<tr valign="bottom">
		<td height="50">{add}</td>
	</tr>
</table>
</center>
