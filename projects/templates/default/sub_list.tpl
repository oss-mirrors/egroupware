<!-- $Id$ -->

{app_header}

<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<table border="0" width="100%">
	<tr>
		<td colspan="9" align="left">
			<table boder="0" width="100%">
				<tr>
				{left}
					<td align="center">{lang_showing}</td>
				{right}
				</tr>
			</table>
		</td>
	</tr>
	<tr colspan="9">
		<td width="33%" align="left">
			<form action="{project_action}" name="form" method="POST">
			<select name="pro_parent" onChange="this.form.submit();"><option value="">{lang_select_project}</option>{project_list}</select>
			<noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript></form></td>
		<td width="33%" align="center"><form method="POST" name="filter" action="{filter_action}">{filter_list}</form></td>
		<td width="33%" align="right"><form method="POST" name="query" action="{search_action}">{search_list}</form></td>
	</tr>
</table>
<table border="0" width="100%" cellpadding="2" cellspacing="2">
	<tr bgcolor="{th_bg}">
		<td width="8%" bgcolor="{th_bg}">{sort_number}</td>
		<td width="20%" bgcolor="{th_bg}">{sort_title}</td>
		<td width="18%" bgcolor="{th_bg}">{sort_coordinator}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{sort_status}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{sort_start_date}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{sort_end_date}</td>
		<td width="10%" bgcolor="{th_bg}" align="center">{lang_h_hours}</td>
		<td width="5%" bgcolor="{th_bg}" align="center">{lang_view}</td>
		<td width="5%" bgcolor="{th_bg}" align="center">{lang_edit}</td>
	</tr>

<!-- BEGIN sub_list -->

	<tr bgcolor="{tr_color}">
		<td>{number}</td>
		<td>{title}</td>
		<td>{coordinator}</td>
		<td align="center">{status}</td>
		<td align="center">{start_date}</td>
		<td align="center">{end_date}</td>
		<td align="center"><a href="{hours}">{lang_hours}</a></td>
		<td align="center"><a href="{view}">{lang_view_entry}</a></td>
		<td align="center"><a href="{edit}">{lang_edit_entry}</a></td>
	</tr>

<!-- END sub_list -->

	<tr valign="bottom">
		<td height="50">{add}</td>
	</tr>
</table>
</center>
