<!-- BEGIN form -->

<br>
<center><font color=red>{messages}</font></center>

<form name="viewTicketDetails" method="POST" action="{viewticketdetails_link}">
<input type="hidden" name="ticket_id" value="{ticket_id}">

<table border="0" width="95%" cellspacing="0" align="center">
	<tr class="th">
		<td colspan="4">&nbsp;<b>[ #{ticket_id} ] - {value_subject}</b></td>
	</tr>
    {duplicate_ticket}

	<tr class="row_on">
		<td width="25%">{lang_caller_name}:</td>
		<td width="25%"><b>{value_caller_name}</b></td>
		<td width="25%">{lang_caller_telephone}:</td>
		<td width="25%"><b>{value_caller_telephone}</b></td>
	</tr>

	<tr class="row_off">
        <td width="25%">{lang_caller_address}:</td>
        <td width="25%"><b>{value_caller_address}</b></td>
        <td width="25%">{lang_caller_address_2}:</td>
        <td width="25%"><b>{value_caller_address_2}</b></td>

	</tr>

	<tr class="row_on">
        <td width="25%">{lang_caller_email}:</td>
        <td width="25%"><b>{value_caller_email}</b></td>
        <td width="25%">{lang_status}:</td>
        <td width="25%"><b>{value_status}</b></td>
	</tr>

	<tr class="row_off">
		<td width="25%">{lang_opendate}:</td>
		<td width="25%"><b>{value_opendate}</b></td>
		<td width="25%">{lang_finishdate}:</td>
		<td width="25%"><b>{value_finishdate}</b></td>
	</tr>

	<tr class="row_on">
         <td ><b>{lang_details}:</b></td>
         <td colspan="3">{value_details}</td>
	</tr>


	<tr height="40">
		<td colspan="4">
			<input type="submit" value="{lang_close_with_new}" name="save"> &nbsp;
                        <input type="submit" value="{lang_close}" name="close"> &nbsp;
			<input type="submit" value="{lang_cancel}" name="cancel">
		</td>
	</tr>

</table>
</form>



<!-- END form -->
