<!-- BEGIN options_select -->
    <option value="{optionvalue}" {optionselected}>{optionname}</option>
<!-- END options_select -->

<!-- BEGIN options_select2 -->
    <option value="{optionvalue2}" {optionselected2}>{optionname2}</option>
<!-- END options_select2 -->


<!-- BEGIN additional_notes_row -->
	<tr class="{row_class}">
		<td colspan="4">
			{lang_date}: &nbsp; {value_date}<br>
			{lang_user}: &nbsp; {value_user}<p>
			{value_note}
		</td>
	</tr>
<!-- END additional_notes_row -->

<!-- BEGIN additional_notes_row_empty -->
	<tr class="row_off">
		<td colspan="4">{lang_no_additional_notes}</b></td>
	</tr>
<!-- END additional_notes_row_empty -->

<!-- BEGIN row_history -->
		<tr class="{row_class}">
			<td>{value_date}</td>
			<td>{value_user}</td>
			<td>{value_action}</td>
			<td>{value_old_value}</td>
			<td>{value_new_value}</td>
		</tr>
<!-- END row_history -->

<!-- BEGIN row_history_empty -->
		<tr class="row_off">
			<td colspan="4" align="center"><b>{lang_no_history}</b></td>
		</tr>
<!-- END row_history_empty -->

<!-- BEGIN form -->
<script type="text/javascript">
var tab = new Tabs(3,'activetab','inactivetab','tab','tabcontent','','','tabpage');
</script>

{CatGroupUser}

<br>
<center><font color=red>{messages}</font></center>

<form name="viewTicketDetails" method="POST" action="{viewticketdetails_link}">
<input type="hidden" name="ticket_id" value="{ticket_id}">
<input type="hidden" name="lstAssignedfrom" value="{ticket_user}">

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
		<td width="25%">{lang_caller_email}:</td>
		<td width="25%"><b>{value_caller_email}</b></td>
		<td width="25%">{lang_caller_telephone_2}:</td>
		<td width="25%"><b>{value_caller_telephone_2}</b></td>		
	</tr>
	
	<tr class="row_on">
		<td width="25%">{lang_caller_address}:</td>
		<td width="25%"><b>{value_caller_address}</b></td>
		<td width="25%">{lang_caller_address_2}:</td>
		<td width="25%"><b>{value_caller_address_2}</b></td>
	</tr>
	
	<tr class="row_off">
		<td width="25%">{lang_caller_ticket_id} / {lang_caller_password}:</td>
		<td width="25%"><b>{value_caller_ticket_id} / {value_caller_password}</b></td>
		<td width="25%">{lang_caller_audio_file}:</td>
		<td width="25%"><b>{value_caller_audio_file}</b></td>
	</tr>

	<tr class="row_on">
		<td>{lang_category}:</td>
		<td><b>{value_category}</b></td>
		<td>{lang_priority}:</td>
		<td><b>{value_priority}</b></td>
	</tr>

	<tr class="row_off">
		<td>{lang_group}:</td>
		<td><b>{value_group}</b></td>
		<td>{lang_assignedto}:</td>
		<td><b>{value_assignedto}</b></td>
	</tr>

	<tr class="row_on">
		<td width="25%">{lang_billable_hours}:</td>
		<td width="25%"><b>{value_billable_hours}</b></td>
		<td>{lang_billable_hours_rate}:</td>
		<td><b>{value_billable_hours_rate}</b></td>
	</tr>


	<tr class="row_off">
		<td width="25%">{lang_opendate}:</td>
		<td width="25%"><b>{value_opendate}</b></td>
		<td width="25%">{lang_assignedfrom}:</td>
		<td width="25%"><b>{value_owner}</b></td>
	</tr>
	
	<tr class="row_on">
		<td>{lang_state}:</td>
		<td><b>{value_state}</b><br>{value_state_description}</td>
		<td>{lang_billable_hours_total}:</td>
		<td><b>{value_billable_hours_total}</b></td>
	</tr>

        <tr class="row_off">
                <td>{lang_caller_satisfaction}:</td>
                <td><b>{value_caller_satisfaction}</b></td>
                <td>{lang_escalation}:</td>
                <td><b>{value_escalation}</b></td>
        </tr>

        <tr class="row_on">
                <td>{lang_caller_solution}:</td>
                <td colspan="3"><b>{value_caller_solution}</b></td>
        </tr>


	<tr>
		<td colspan="4"><br>
			<table width="100%" border="0" cellspacing="0" cellpading="0">
				<tr>
					<th id="tab1" class="activetab" onclick="javascript:tab.display(1);"><a href="#" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);">&nbsp; {lang_details} &nbsp;</a></th>
					<th id="tab2" class="activetab" onclick="javascript:tab.display(2);"><a href="#" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); return(false);">&nbsp; {lang_update} &nbsp;</a></th>
					<th id="tab3" class="activetab" onclick="javascript:tab.display(3);"><a href="#" tabindex="0" accesskey="3" onfocus="tab.display(3);" onclick="tab.display(3); return(false);">&nbsp; {lang_history} &nbsp;</a></th>
				</tr>
			</table>

			<div id="tabcontent1" class="inactivetab">
				<table class="tabcontent" border="0" width="100%" cellspacing="0">
					<tr class="th">
						<td colspan="4"><b>{lang_details}:</b></td>
					</tr>

					<tr class="row_off">
						<td colspan="4">{value_details}</td>
					</tr>

					<tr class="th">
						<td colspan="4"><b>{lang_additional_notes}:</b></td>
					</tr>
{rows_notes}
				</table>
			</div>

			<div id="tabcontent2" class="inactivetab">
				<table class="tabcontent" border="0" width="100%" cellspacing="0">
					<tr class="th">
						<td colspan="4"><b>{lang_update}:</b></td>
					</tr>


				   {modify_subject}

					<tr class="row_on">
                       	<td>{lang_caller_name}:</td>
                       	<td><input name="ticket[caller_name]" value="{value_caller_name}" size="30" {disabled_for_standard_user}></td>
                        <td>{lang_caller_telephone}:</td>
                       	<td><input name="ticket[caller_telephone]" value="{value_caller_telephone}" size="20" {disabled_for_standard_user}></td>
                    </tr>

					<tr class="row_off">
						<td >{lang_caller_email}:</td>
						<td><input name="ticket[caller_email]" value="{value_caller_email}" size="30" {disabled_for_standard_user}></td>
						<td>{lang_caller_telephone_2}:</td>
						<td><input name="ticket[caller_telephone_2]" value="{value_caller_telephone_2}" size="20" {disabled_for_standard_user}></td>
					</tr>

					<tr class="row_on">
						<td >{lang_caller_address}:</td>
						<td><input name="ticket[caller_address]" value="{value_caller_address}" size="40" {disabled_for_standard_user}></td>
						<td >{lang_caller_address_2}:</td>
						<td><input name="ticket[caller_address_2]" value="{value_caller_address_2}" size="40" {disabled_for_standard_user}></td>

					</tr>

					<tr class="row_on">
						<td >{lang_caller_ticket_id} / {lang_caller_password}:</td>
						<td><input name="ticket[caller_ticket_id]" value="{value_caller_ticket_id}" size="10" {disabled_for_standard_user}>&nbsp;/&nbsp;
                            <input name="ticket[caller_password]" value="{value_caller_password}" size="10" {disabled_for_standard_user}>
                        </td>
						<td >{lang_caller_audio_file}:</td>
						<td><input name="ticket[caller_audio_file]" value="{value_caller_audio_file}" size="40" {disabled_for_standard_user}></td>
					</tr>

					<tr class="row_off">
						<td>{lang_category}:</td>
						<td>{value_category2}</td>
						<td>{lang_priority}:</td>
						<td><select name="ticket[priority]" {disabled_for_standard_user}>{options_priority}</select></td>
					</tr>

					<tr class="row_on">
						<td>{lang_group}:</td>
                       	                        <td>{value_group2}</td>
						<td>{lang_assignedto}:</td>
  						<td>{value_assignedto2}</td>
					</tr>

					<tr class="row_off">
						<td>{lang_billable_hours}:</td>
						<td><input name="ticket[billable_hours]" value="{value_billable_hours}" size="5" {disabled_for_standard_user}></td>
						<td>{lang_billable_hours_rate}:</td>
						<td><input name="ticket[billable_rate]" value="{value_billable_hours_rate}" size="5" {disabled_for_standard_user}></td>
					</tr>

					<tr class="row_on">
						<td>{lang_additional_notes}:</td>
						<td colspan="3">{additonal_details_rows}<textarea rows="12" name="ticket[note]" cols="70" wrap="physical" {disabled_for_admin_user}></textarea></td>
					</tr>

					<tr class="row_off">
						<td>{lang_status}:</td>
						<td colspan="3"><select name="ticket[status]" {disabled_field_status}>{options_status}</select></td>
					</tr>
					<tr class="row_on">
						<td>{lang_update_state}:</td>
						<td colspan="3"><input name="ticket[state]" type="radio" value="0" CHECKED {disabled_for_admin_user}>{lang_keep_present_state}</td>
					</tr>
<!-- BEGIN update_state_items -->
					<tr class="row_on">
						<td>&nbsp;</td>
						<td colspan="3"><input name="ticket[state]" type="radio" value="{update_state_value}" {disabled_for_admin_user}>{update_state_text}</td>
					</tr>
<!-- END update_state_items -->

                                  	<tr class="row_off">
                                                <td>{lang_caller_satisfaction}:</td>
                                                <td colspan="3"><select name="ticket[caller_satisfaction]" {disabled_for_standard_user}>{options_caller_satisfaction}</select></td>
                                        </tr>

                                  	<tr class="row_off">
                                                <td>{lang_caller_solution}:</td>
                                                <td colspan="3"><textarea rows="8" name="ticket[caller_solution]" cols="70" wrap="physical" {disabled_for_standard_user}>{value_caller_solution}</textarea></td>
                                        </tr>

				</table>
			</div>

			<div id="tabcontent3" class="inactivetab">
				<table class="tabcontent" border="0" width="100%" cellspacing="0">
					<tr class="th">
						<td colspan="4"><b>{lang_history}</b></td>
					</tr>

					<tr>
						<td colspan="4">

							<table border="0" width="100%">
								<tr class="th">
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
			</div>

		</td>
	</tr>

	<tr height="40">
		<td colspan="4">
			<input type="submit" value="{lang_save}" name="save"> &nbsp;
			<input type="submit" value="{lang_apply}" name="apply"> &nbsp;
			<input type="submit" value="{lang_cancel}" name="cancel">
		</td>
	</tr>

</table>
</form>

{initCatGroupUser}

<!-- END form -->
