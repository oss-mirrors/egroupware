<!-- $Id$ -->

{app_header}

<center>
{message}
<table width="75%" border="0" cellspacing="2" cellpadding="2">
<form method="POST" action="{actionurl}">
{hidden_vars}
	<tr>
		<td>{lang_pro_parent}</td>
		<td>{pro_parent}</td>
	</tr>
	<tr>
		<td>{lang_project}:</td>
		<td>{project_name}</td>
	</tr>
	<tr>
		<td>{lang_activity}:</td>
		<td><select name="values[activity_id]">{activity_list}</select></td>
	</tr>
	<tr>
		<td>{lang_descr}:</td>
		<td><input type="text" name="values[hours_descr]" size="50" value="{hours_descr}"></td>
	</tr>
	<tr>
		<td>{lang_remark}:</td>
		<td colspan="2"><textarea name="values[remark]" rows="5" cols="50" wrap="VIRTUAL">{remark}</textarea></td>
	</tr>
	<tr>
		<td height="35"><b>{lang_work_date}</b></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>{lang_start_date}:</td>
		<td>{start_date_select}</td>
	</tr>
	<tr>
		<td>{lang_end_date}:</td>
		<td>{end_date_select}</td>
	</tr>
	<tr>
		<td height="35"><b>{lang_work_time}</b></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>{lang_start_time}:</td>
		<td>
			<input type="text" name="values[shour]" value="{shour}" size="3" maxlength="2">
			<input type="text" name="values[smin]" value="{smin}" size="3" maxlength="2">
			&nbsp;{sradio}
		</td>
	</tr>
	<tr>
		<td>{lang_end_time}:</td>
		<td>
			<input type="text" name="values[ehour]" value="{ehour}" size=3 maxlength=2>
			<input type="text" name="values[emin]" value="{emin}" size=3 maxlength=2>
			&nbsp;{eradio}
		</td>
	</tr>
	<tr>
		<td>{lang_hours}:</td>
		<td>
			<input type="text" name="values[hours]" value="{hours}" size=3 maxlength=2>
			<input type="text" name="values[minutes]" value="{minutes}" size=3 maxlength=2>
		</td>
	</tr>
	<tr>
		<td>{lang_status}:</td>
		<td><select name="values[status]">{status_list}</select></td>
	</tr>
	<tr>
		<td>{lang_employee}:</td>
		<td><select name="values[employee]">{employee_list}</select></td>
	</tr>
</table>

<!-- BEGIN add -->
         
<table width="75%" border="0" cellspacing="2" cellpadding="2">
	<tr valign="bottom">
		<td height="50"><input type="submit" name="values[submit]" value="{lang_save}"></td>
		<td height="50"><input type="reset" name="reset" value="{lang_reset}"></form></td>
		<td height="50"><form method="POST" action="{doneurl}"> 
			<input type="submit" name="done" value="{lang_done}"></form></td>
	</tr>
</table>
</center>
         
<!-- END add -->
        
<!-- BEGIN edit -->

<table width="75%" border="0" cellspacing="2" cellpadding="2">
	<tr valign="bottom">
		<td height="50"><input type="submit" name="values[submit]" value="{lang_save}">
			</form></td>
		<td height="50">{delete}</td>
		<td height="50">
			<form method="POST" action="{doneurl}">
			<input type="submit" name="done" value="{lang_done}"></form></td>
	</tr>
</table>
</center>

<!-- END edit -->
