<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table border="0" align="center" width="100%">
   <tr bgcolor="{th_bg}">
	   <td colspan="2" align="center"><font color="{th_text}"><b>{title}</b></font></td>
   </tr>
<!-- END header -->

<!-- BEGIN body -->
   <tr bgcolor="{row_off}">
    <td colspan="2"><b>{lang_accounting}&nbsp;{lang_settings}</b></td>
   </tr>
	<tr bgcolor="{row_off}">
		<td>{lang_hours_of_work_day}:</td>
		<td><input type="text" name="newsettings[hwday]" value="{value_hwday}" size="3" maxlength="2">&nbsp;[hh]</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_project_accounting}:</td>
		<td>
			<select name="newsettings[accounting]">
				<option value="own"{selected_accounting_own}>{lang_definition_per_project}</option>
				<option value="activity"{selected_accounting_activity}>{lang_use_activities}</option>
			</select>
		</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td colspan="2">{lang_if_using_activities}:</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_Invoicing_of_work_time}:</td>
		<td>
			<select name="newsettings[activity_bill]">
				<option value="h"{selected_activity_bill_h}>{lang_Exact_accounting_[hh.mm]}</option>
				<option value="wu"{selected_activity_bill_wu}>{lang_per_workunit}</option>
			</select>
		</td>
	</tr>
   <tr bgcolor="{row_on}">
    <td colspan="2"><b>{lang_project_dependencies}</b></td>
   </tr>
	<tr bgcolor="{row_on}">
		<td>{lang_move_start_date_if_pervious_projects_end_date_changes}:</td>
		<td>
			<select name="newsettings[dateprevious]">
				<option value="no"{selected_dateprevious_no}>{lang_no}</option>
				<option value="yes"{selected_dateprevious_yes}>{lang_yes}</option>
			</select>
		</td>
	</tr>
	</table>
	<table align="center" width="100%">
	<tr>
		<td>
			&nbsp;
		</td>
		<td>
			<table width="100%">
				<tr width="100%">
					<td align="center" width="33%">
						<small>{lang_onadd}</small>
					</td>
					<td align="center" width="33%">
						<small>{lang_onstatuschange}</small>
					</td>
					<td align="center" width="33%">
						<small>{lang_ondelete}</small>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>
			{lang_notify_management_committee}:
		</td>
		<td>
			<table width="100%">
				<tr width="100%">
					<td align="center" width="33%">
						<select name="newsettings[notifymanagementcmte_new]" size="1">
							<option value="no"{selected_notifymanagementcmte_new_no}>{lang_no}</option>
							<option value="yes"{selected_notifymanagementcmte_new_yes}>{lang_yes}</option>
						</select>
					</td>
					<td align="center" width="33%">
						<select name="newsettings[notifymanagementcmte_status_change]" size="1">
							<option value="no"{selected_notifymanagementcmte_status_change_no}>{lang_no}</option>
							<option value="yes"{selected_notifymanagementcmte_status_change_yes}>{lang_yes}</option>
						</select>
					</td>
					<td align="center" width="33%">
						<select name="newsettings[notifymanagementcmte_delete]" size="1">
							<option value="no"{selected_notifymanagementcmte_delete_no}>{lang_no}</option>
							<option value="yes"{selected_notifymanagementcmte_delete_yes}>{lang_yes}</option>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>
			{lang_notify_project_administrators}:
		</td>
		<td>
			<table width="100%">
				<tr width="100%">
					<td align="center" width="33%">
						<select name="newsettings[notifyprojectadmins_new]" size="1">
							<option value="no"{selected_notifyprojectadmins_new_no}>{lang_no}</option>
							<option value="yes"{selected_notifyprojectadmins_new_yes}>{lang_yes}</option>
						</select>
					</td>
					<td align="center" width="33%">
						<select name="newsettings[notifyprojectadmins_status_change]" size="1">
							<option value="no"{selected_notifyprojectadmins_status_change_no}>{lang_no}</option>
							<option value="yes"{selected_notifyprojectadmins_status_change_yes}>{lang_yes}</option>
						</select>
					</td>
					<td align="center" width="33%">
						<select name="newsettings[notifyprojectadmins_delete]" size="1">
							<option value="no"{selected_notifyprojectadmins_delete_no}>{lang_no}</option>
							<option value="yes"{selected_notifyprojectadmins_delete_yes}>{lang_yes}</option>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>
			{lang_notify_sales_department}:
		</td>
		<td>
			<table width="100%">
				<tr width="100%">
					<td align="center" width="33%">
						<select name="newsettings[notifysalesdept_new]" size="1">
							<option value="no"{selected_notifysalesdept_new_no}>{lang_no}</option>
							<option value="yes"{selected_notifysalesdept_new_yes}>{lang_yes}</option>
						</select>
					</td>
					<td align="center" width="33%">
						<select name="newsettings[notifysalesdept_status_change]" size="1">
							<option value="no"{selected_notifysalesdept_status_change_no}>{lang_no}</option>
							<option value="yes"{selected_notifysalesdept_status_change_yes}>{lang_yes}</option>
						</select>
					</td>
					<td align="center" width="33%">
						<select name="newsettings[notifysalesdept_delete]" size="1">
							<option value="no"{selected_notifysalesdept_delete_no}>{lang_no}</option>
							<option value="yes"{selected_notifysalesdept_delete_yes}>{lang_yes}</option>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<!-- END body -->
<!-- BEGIN footer -->
  <tr height="50" valign="bottom">
    <td><input type="submit" name="submit" value="{lang_submit}"></td>
	<td align="right"><input type="submit" name="cancel" value="{lang_cancel}"></td>
  </tr>
</table>
</form>
<!-- END footer -->
