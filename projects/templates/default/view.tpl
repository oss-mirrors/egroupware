<!-- $Id$ -->

{app_header}

<center>
<table width="85%" border="0" cellspacing="3" cellpadding="3">

	<tr bgcolor="{row_off}">
		<td><b>{lang_parent}</b></td>
		<td>{pro_parent}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_investment_nr}</b></td>
		<td>{investment_nr}</td>
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
		<td><b>{lang_bookable_activities}:</b></td>
		<td>{book_activities_list}&nbsp;</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_billable_activities}:</b></td>
		<td>{bill_activities_list}&nbsp;</td>
	</tr>
	<tr height="15">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_budget}:&nbsp;{currency}</b></td>
		<td>{budget}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_pcosts}&nbsp;{month}:&nbsp;{currency}</b></td>
		<td>{pcosts}</td>
	</tr>
	<tr height="15">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_ptime}:&nbsp;{lang_hours}</b></td>
		<td>{phours}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_utime}:&nbsp;{lang_hours}</b></td>
		<td>{uhours}</td>
	</tr>
	<tr height="15">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_start_date}:</b></td>
		<td>{sdate}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_end_date}:</b></td>
		<td>{edate}</td>
	</tr>
	<tr height="15">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_creator}:</b></td>
		<td>{owner}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_cdate}:</b></td>
		<td>{cdate}</td>
	</tr>
	<tr height="15">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td><b>{lang_processor}:</b></td>
		<td>{processor}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td><b>{lang_last_update}:</b></td>
		<td>{udate}</td>
	</tr>
</table>

<!-- BEGIN done -->
         
<table width="85%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td height="50">
			<form method="POST" action="{done_action}">
			<input type="submit" name="done" value="{lang_done}">
			</form></td>
	</tr>
</table>
</center>

<!-- END done -->
