<!-- $Id$ -->
<script language="JavaScript">
	self.name="first_Window";
	function abook()
	{
		Window1=window.open('{addressbook_link}',"Search","width=800,height=600,toolbar=no,scrollbars=yes,resizable=yes");
	}
</script>

{app_header}

<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<form method="POST" name="projects_form" action="{actionurl}">
{pref_message}<br>{message}
<table width="85%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td>{lang_parent}</td>
		<td>{pro_parent}</td>
	</tr>
	<tr>
		<td>{lang_choose}</td>
		<td>{choose}</td>
	</tr>
	<tr>
		<td>{lang_number}:</td>
		<td><input type="text" name="values[number]" value="{number}"></td>
	</tr>
	<tr>
		<td>{lang_title}:</font></td>
		<td><input type="text" name="values[title]" size="50" value="{title}"></td>
	</tr>
	<tr>
		<td>{lang_descr}:</td>
		<td colspan="2"><textarea name="values[descr]" rows="4" cols="50" wrap="VIRTUAL">{descr}</textarea></td>
	</tr>
	<tr>
		<td>{lang_category}:</td>
		<td>{cat}</td>
	</tr>
	<tr>
		<td><input type="hidden" name="abid" value="{abid}">
			{customer}</td>
	</tr>
	<tr>
		<td>{lang_coordinator}:</td>
		<td><select name="values[coordinator]">{coordinator_list}</select></td>
	</tr>
	<tr>
		<td>{lang_status}:</td>
		<td><select name="values[status]">{status_list}</select></td>
	</tr>
	<tr>
		<td>{lang_budget}:&nbsp;{currency}</td>
		<td><input type="text" name="values[budget]" value="{budget}"></td>
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
		<td>{lang_bookable_activities}:</td>
		<td>{book_activities_list}</td>
	</tr>
	<tr>
		<td>{lang_billable_activities}:</td>
		<td>{bill_activities_list}</td>
	</tr>
	<tr>
		<td>{lang_access}:</td>
		<td>{access}</td>
	</tr>
</table>

<!-- BEGIN add -->

<table width="50%" border="0" cellspacing="2" cellpadding="2">
	<tr valign="bottom">
		<td height="50">
			{hidden_vars}
			<input type="submit" name="submit" value="{lang_save}"></td>
		<td height="50"><input type="reset" name="reset" value="{lang_reset}"></form></td>
		<td height="50">
			{hidden_vars}
			<form method="POST" action="{done_url}">
			<input type="submit" name="done" value="{lang_done}"></form></td>
	</tr>
</table>
</center>
         
<!-- END add -->
        
<!-- BEGIN edit -->

<table width="50%" border="0" cellspacing="2" cellpadding="2">
	<tr valign="bottom">
		<td height="50">
			{hidden_vars}
			<input type="submit" name="submit" value="{lang_save}"></form></td>
		<td height="50">
			{hidden_vars}{delete}</td>
		<td height="50">
			<form method="POST" action="{done_url}">
			{hidden_vars}
			<input type="submit" name="done" value="{lang_done}"></form></td>
	</tr>
</table>
</center>

<!-- END edit -->
