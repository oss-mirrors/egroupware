
<!-- BEGIN options_select -->
    <option value="{optionvalue}" {optionselected}>{optionname}</option>
<!-- END options_select -->



<!-- BEGIN form -->



<br>


{CatGroupUser}

{messages}

<form name="newTicket" method="POST" action="{form_action}">

<table border="0" width="80%" cellspacing="0" align="center">
	<tr class="th">
		<td colspan="4">&nbsp;</td>
	</tr>
	
	<tr class="row_off">
		<td align="left">{lang_caller_name}:</td>
		<td align="left"><input name="ticket_caller_name" value="{value_caller_name}"></td>
		<td align="left">{lang_caller_telephone}:</td>
		<td align="left"><input name="ticket_caller_telephone" value="{value_caller_telephone}"></td>
	</tr>
	
	<tr class="row_on">
		<td align="left">{lang_caller_email}:</td>
		<td align="left"><input name="ticket_caller_email" value="{value_caller_email}"></td>
		<td align="left">{lang_caller_telephone_2}:</td>
		<td align="left"><input name="ticket_caller_telephone_2" value="{value_caller_telephone_2}"></td>
	</tr>

	<tr class="row_off">
		<td align="left">{lang_caller_address}:</td>
		<td align="left"><input name="ticket_caller_address" value="{value_caller_address}"></td>
		<td align="left">{lang_caller_address_2}:</td>
		<td align="left"><input name="ticket_caller_address" value="{value_caller_address_2}"></td>
	</tr>

	<tr class="row_on">
        <td align="left">{lang_caller_ticket_id}:</td>
        <td align="left"><input name="ticket_caller_ticket_id" value="{value_caller_ticket_id}"></td>
		<td align="left">{lang_caller_password}:</td>
		<td align="left"><input name="ticket_caller_password" value="{value_caller_password}"></td>
	</tr>

	<tr class="row_off">
        <td align="left">{lang_category}:</td>
        <td align="left">{value_category}</td>
        <td align="left">{lang_priority}:</td>
        <td align="left">{value_priority}</td>
	</tr>

	<tr class="row_on">
        <td align="left">{lang_group}:</td>
        <td align="left">{value_group}</td>
        <td align="left">{lang_assignedto}:</td>
        <td align="left">{value_assignedto}</td>
	</tr>

	<tr class="row_off">
        <td align="left">{lang_billable_hours}:</td>
        <td align="left"><input name="ticket_billable_hours" value="{value_billable_hours}"></td>
        <td align="left">{lang_billable_hours_rate}:</td>
        <td align="left"><input name="ticket_billable_rate" value="{value_billable_hours_rate}"></td>
	</tr>

	<tr class="row_on">
		<td align="left">{lang_initialstate}:</td>
		<td align="left"><select name="ticket_state">{options_state}</select></b></td>
        <td align="left">{lang_caller_audio_file}:</td>
        <td align="left"><input name="ticket_caller_audio_file" value="{value_caller_audio_file}"></td>
	</tr>

	<tr class="row_off">
		<td>{lang_subject}<font color="#FF0000">*</font>:</td>
		<td colspan="3"><input name="ticket_subject" value="{value_subject}" size="65"></td>
	</tr>

	<tr class="row_on">
		<td>{lang_details}:</td>
		<td colspan="3"><textarea rows="10" name="ticket_details" cols="65" wrap="hard">{value_details}</textarea></td>
	</tr>

	<tr>
 		<td colspan="2" align="left" height="40">
 			<input type="submit" name="submit" value="{lang_submit}"> &nbsp;
 			<input type="submit" name="cancel" value="{lang_cancel}">
 		</td>
	</tr>
</table>

</form>

{initCatGroupUser}

<!-- END form -->




