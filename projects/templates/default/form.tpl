<!-- $Id$ -->
<script language="JavaScript">
	self.name="first_Window";
	function abook()
	{
		Window1=window.open('{addressbook_link}',"Search","width=800,height=600,toolbar=no,scrollbars=yes,resizable=yes");
	}
</script>
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<form method="POST" name="projects_form" action="{actionurl}">
{error}{message}
{hidden_vars}
<table width="85%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td>{lang_choose}</td>
		<td>{choose}</td>
	</tr>
	<tr>
		<td>{lang_num}:</td>
		<td><input type="text" name="num" value="{num}"></td>
	</tr>
	<tr>
		<td>{lang_title}:</font></td>
		<td><input type="text" name="title" size="50" value="{title}"></td>
	</tr>
	<tr>
		<td>{lang_descr}:</td>
		<td colspan="2"><textarea name="descr" rows=4 cols=50 wrap="VIRTUAL">{descrval}</textarea></td>
	</tr>
	<tr>
		<td>{lang_category}:</td>   
		<td><select name="new_cat"><option value="">{lang_select_cat}</option>{category_list}</select></font></td>
	</tr>
	<tr>
		<td><input type="button" value="{lang_customer}" onClick="abook();"></td>
		<td><input type="hidden" name="abid" value="{abid}">
			<input type="text" name="name" size="50" value="{name}" readonly>&nbsp;&nbsp;&nbsp;{lang_select}</td>
	</tr>
	<tr>
		<td>{lang_coordinator}:</td>
		<td><select name="coordinator">{coordinator_list}</select></td>
	</tr>
	<tr>
		<td>{lang_status}:</td>
		<td><select name="status">{status_list}</select></td>
	</tr>
	<tr>
		<td>{lang_budget}:&nbsp;{currency}</td>
		<td><input type="text" name="budget" value="{budget}"></td>
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
		<td><select name="ba_activities[]" multiple>{ba_activities_list}</select></td>
	</tr>
	<tr>
		<td>{lang_billable_activities}:</td>
		<td><select name="bill_activities[]" multiple>{bill_activities_list}</select></td>
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
			<input type="submit" name="submit" value="{lang_add}"></td>
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
			<input type="submit" name="submit" value="{lang_edit}"></form></td>
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
