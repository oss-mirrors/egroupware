<!-- $Id$ -->

{app_header}

<center>
<table width="85%" border="0" cellspacing="3" cellpadding="3">
	<tr bgcolor="{row_on}">
		<td><b>{lang_parent}</b></td>
		<td>{pro_parent}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_number}:</b></td>
		<td>{number}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_title}:</b></td>
		<td>{title}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_descr}:</b></td> 
		<td>{descr}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_customer}:</b></td>
		<td>{customer}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_category}:</b></td>
		<td>{cat}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_coordinator}:</b></td>
		<td>{coordinator}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_status}:</b></td>
		<td>{status}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_budget}:</b></td>
		<td>{currency}&nbsp;{budget}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_start_date}:</b></td>
		<td>{sdate}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_end_date}:</b></td>
		<td>{edate}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_bookable_activities}:</b></td>
		<td>{book_activities_list}&nbsp;</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_billable_activities}:</b></td>
		<td>{bill_activities_list}&nbsp;</td>
	</tr>
</table>
         
<!-- BEGIN done -->
         
<table width="85%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td height="50">
			<form method="POST" action="{done_action}">
			<input type="submit" name="submit" value="{lang_done}">
			</form></td>
	</tr>
</table>
</center>

<!-- END done -->
