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
		<td width="25%" align="left">
			<form method="POST" action="{project_action}" name="project_id">
			<select name="project_id" onChange="this.form.submit();"><option value="">{lang_select_project}</option>{project_list}</select>
			<noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript></form></td>
		<td width="25%" align="center"><form method="POST" name="filter" action="{filter_action}">{filter_list}</form></td>
		<td width="25%" align="center">
			<form method="POST" action="{state_action}" name="state">
			<select name="state" onChange="this.form.submit();">{state_list}</select>
			<noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript></form></td>
		<td width="25%" align="right"><form method="POST" name="query" action="{search_action}">{search_list}</form></td>
	</tr>
</table>

{error}<br>

<table border="0" width="100%" cellpadding="2" cellspacing="2">
	<tr bgcolor="{th_bg}">
		<td width="20%" bgcolor="{th_bg}">{sort_hours_descr}</td>
		<td width="10%" bgcolor="{th_bg}" align="center">{sort_status}</td>
		<td width="10%" bgcolor="{th_bg}" align="center">{sort_start_date}</td>
		<td width="10%" bgcolor="{th_bg}" align="center">{sort_start_time}</td>
		<td width="10%" bgcolor="{th_bg}" align="center">{sort_end_time}</td>
		<td width="5%" bgcolor="{th_bg}" align="right">{sort_hours}</td>
		<td width="15%" bgcolor="{th_bg}">{sort_employee}</td>
		<td width="5%" align="center">{lang_view}</td>
		<td width="5%" align="center">{lang_edit}</td>
	</tr>
  
<!-- BEGIN hours_list -->

	<tr bgcolor="{tr_color}">
		<td>{hours_descr}</td>
		<td align="center">{status}</td>
		<td align="center">{start_date}</td>
		<td align="center">{start_time}</td>
		<td align="center">{end_time}</td>
		<td align="right">{minutes}</td>
		<td>{employee}</td>
		<td align="center"><a href="{view}">{lang_view}</a></td>
		<td align="center"><a href="{edit}">{lang_edit_entry}</a></td>
	</tr>

<!-- END hours_list -->

<!-- BEGINN add   -->

	<tr>
		<td valign="bottom" height="50">
			{action}</td>
	</tr>

<!-- END add -->

</table>
</center>
