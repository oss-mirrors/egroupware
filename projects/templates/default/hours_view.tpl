<!-- $Id$ -->

{app_header}

<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
{error}
<table width="85%" border="0" cellspacing="3" cellpadding="3">
	<tr bgcolor="{row_on}">
		<td colspan="2">{lang_project}:</td>
		<td>{project_name}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td colspan="2">{lang_activity}:</td>
		<td>{activity}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td colspan="2">{lang_minperae}:</td>
		<td>{minperae}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td colspan="2">{lang_billperae}:&nbsp;{currency}</td>
		<td>{billperae}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td colspan="2">{lang_descr}:</td>
		<td>{hours_descr}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td colspan="2">{lang_remark}:</td>
		<td>{remark}</td>
	</tr>
	<tr height="5">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td colspan="2"><b>{lang_work_date}</b></td>
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td colspan="2">{lang_start_date}:</td>
		<td>{sdate}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td colspan="2">{lang_end_date}:</td>
		<td>{edate}</td>
	</tr>
	<tr height="5">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td colspan="2"><b>{lang_work_time}</b></td>
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td colspan="2">{lang_start_time}:</td>
		<td>{stime}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td colspan="2">{lang_end_time}:</td>
		<td>{etime}</td>
	</tr>
	<tr height="5">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td colspan="2">{lang_hours}:</td>
		<td>{hours}&nbsp;:&nbsp;{minutes}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td colspan="2">{lang_status}:</td>
		<td>{status}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td colspan="2">{lang_employee}:</td>
		<td>{employee}</td>
	</tr>
</table>
        
<!-- BEGIN done -->

<table width="85%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td height="50">
			<form method="POST" action="{doneurl}">
			<input type="submit" name="done" value="{lang_done}"></form></td>
	</tr>
</table>
</center>

<!-- END done -->
