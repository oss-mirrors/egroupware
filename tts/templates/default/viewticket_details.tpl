<!-- BEGIN options_select -->
    <option value="{optionvalue}" {optionselected}>{optionname}</option>
<!-- END options_select -->

<!-- BEGIN additional_notes_row -->
	<tr bgcolor="{row_off}">
		<td colspan="4">
			<hr>
			{lang_date}: &nbsp; {value_date}
			<br>{lang_user}: &nbsp; {value_user}
			<br><br>{value_note}
			<p>
		</td>
	</tr>
<!-- END additional_notes_row -->

<!-- BEGIN additional_notes_row_empty -->
	<tr bgcolor="{row_off}">
		<td colspan="4">{lang_no_additional_notes}</b></td>
	</tr>
<!-- END additional_notes_row_empty -->

<!-- BEGIN row_history -->
		<tr bgcolor="{tr_color}">
			<td>{value_date}</td>
			<td>{value_user}</td>
			<td>{value_action}</td>
			<td>{value_old_value}</td>
			<td>{value_new_value}</td>
		</tr>
<!-- END row_history -->

<!-- BEGIN row_history_empty -->
		<tr bgcolor="{row_off}">
			<td colspan="4" align="center"><b>{lang_no_history}</b></td>
		</tr>
<!-- END row_history_empty -->

<!-- BEGIN form -->
<b>{lang_viewjobdetails}</b>
<hr><p>

<center><font color=red>{messages}</font></center>

<form method="POST" action="{viewticketdetails_link}">
<input type="hidden" name="ticket_id" value="{ticket_id}">
<input type="hidden" name="lstAssignedfrom" value="{ticket_user}">

<table border="0" width="80%" cellspacing="0" align="center">
	<tr bgcolor="{th_bg}">
		<td colspan="4">&nbsp;<b>[ #{ticket_id} ] - {value_subject}</b></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_opendate}:</td>
		<td align="left"><b>{value_opendate}</b></td>
		<td align="left">{lang_billable_hours}:</td>
		<td align="left"><b>$ {value_billable_hours}</b></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_assignedfrom}:</td>
		<td align="left"><b>{value_owner}</b></td>
		<td align="left">{lang_billable_hours_rate}:</td>
		<td align="left"><b>$ {value_billable_hours_rate}</b></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_assignedto}:</td>
		<td align="left"><b>{value_assignedto}</b></td>
		<td align="left">{lang_billable_hours_total}:</td>
		<td align="left"><b>$ {value_billable_hours_total}</b></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_priority}:</td>
		<td align="left"><b>{value_priority}</b></td>
		<td align="left">&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_category}:</td>
		<td align="left"><b>{value_category}</b></td>
		<td align="left">&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_group}:</td>
		<td align="left"><b>{value_group}</b></td>
		<td align="left">&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4" align="center"><hr></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4"><b>{lang_details}:</b><br>{value_details}</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4"><b>{lang_additional_notes}:</b></td>
	</tr>

{rows_notes}

	<tr bgcolor="{row_off}">
		<td colspan="4">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4"><b>{lang_update}:</b><hr></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="center"><b>{lang_priority}:</b><select name="ticket[priority]">{options_priority}</select></td>
		<td align="center"><b>{lang_category}:</b><select size="1" name="ticket[category]">{options_category}</select></td>
		<td colspan="2" align="center"><b>{lang_assignedto}:</b><select size="1" name="ticket[assignedto]">{options_assignedto}</select></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="right"><b>{lang_status}:</b><select name="ticket[status]">{options_status}</select></td>
		<td align="right"><b>{lang_group}:</b><select name="ticket[group]">{options_group}</select></td>
		<td><b>{lang_billable_hours_rate}: <input name="ticket[billable_rate]" value="{value_billable_hours_rate}"></td>
		<td><b>{lang_billable_hours}: <input name="ticket[billable_hours]" value="{value_billable_hours}"></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4" align="center">{additonal_details_rows}<textarea rows="12" name="ticket[note]" cols="70" wrap="physical"></textarea></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td>&nbsp;</td>
		<td align="center"><input type="submit" value="{lang_ok}" name="submit"></td>
		<td colspan="2">&nbsp;</td>
	</tr>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>

		<tr>
			<td colspan="4">{lang_history}</td>
		</tr>

		<tr>
			<td colspan="4">

				<table border="0" width="100%">
					<tr bgcolor="{th_bg}">
						<td width="10%">{lang_date}</td>
						<td>{lang_user}</td>
						<td>{lang_action}</td>
						<td>{lang_old_value}</td>
						<td>{lang_new_value}</td>
					</tr>
{rows_history}
				</table>

			</td>
		</tr>

   </table>
</form>
<!-- END form -->
