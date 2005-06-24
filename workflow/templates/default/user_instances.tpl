{priority_css}
<div style="color:red; text-align:center">{message}</div>
{user_tabs}
<form name="userInstancesForm" action="{form_action}" method="post">
<input type="hidden" name="start" value="0" />
<table style="border: 0;width:100%;">
	<tr class="th">
		<td style="font-size: 120%; font-weight:bold; width=100%">
			{lang_List_of_instances}
		</td>
	</tr>
</table>
<table style="border: 0;width:100%;">
	<tr class="row_on">
		<td>
			{lang_Process}
		</td>
		<td>
                        {lang_Activity}
                </td>
		<td>
			{lang_User}
		</td>
		<td>
			{lang_more_options?}
		</td>
		<td>
			{lang_Search}
		</td>
		<td rowspan="2" width="100">
			<div style='text-align:center;'><input type="submit" name="filter" value="{lang_Reload_filter}" /></div>
		</td>	
	</tr>
	<tr class="row_off">
		<td >
			<select {filters_on_change} name="filter_process">
				<option {selected_filter_process_all} value="">{lang_All}</option>
				<!-- BEGIN block_select_process -->
				<option {selected_filter_process} value="{filter_process_id}">{filter_process_name} {filter_process_version}</option>
				<!-- END block_select_process -->
			</select>
		</td>
		<td >
			<select {filters_on_change} name="filter_activity_name" >
				<option {selected_filter_activity_all} value="">{lang_All}</option>
				<!-- BEGIN block_select_activity -->
				<option {selected_filter_activity} value="{filter_activity_name}">{filter_activity_name}</option>
				<!-- END block_select_activity -->
			</select>
		</td>
		<td>
			<select {filters_on_change} name="filter_user">
				<option {filter_user_all} value="">{lang_All}</option>
				<option {filter_user_star} value="*">*</option>
				<option {filter_user_user} value="{filter_user_id}">{filter_user_name}</option>
			</select>
		</td>
		<td>
			<input type="checkbox" onClick='this.form.submit();' name="advanced_search" {advanced_search} />
		</td>
		<td>
			<input size="18" type="text" name="find" value="{search_str}" />
		</td>
	</tr>
</table>
{Advanced_table}	
</form>
<table style="border: 0px;width:100%; margin:0 auto">
	<tr class="row_off">
        	{left}
        <td><div align="center">{lang_showing}</div></td>
                {right}
        </tr>
</table>

<form name="userInstancesForm2" action="{form_action}" method="post">
<input type="hidden" name="filter_process" value="{filter_process_id_set}">
<input type="hidden" name="filter_activity_name" value="{filter_activity_name_set}">
<input type="hidden" name="filter_user" value="{filter_user_id_set}">
<input type="hidden" name="advanced_search" value="{advanced_search_set}" />
<input type="hidden" name="find" value="{search_str}" />
<input type="hidden" name="add_exception_instances" value="{add_exception_instances_set}" />
<input type="hidden" name="add_completed_instances" value="{add_completed_instances_set}" />
<input type="hidden" name="add_aborted_instances" value="{add_aborted_instances_set}" />
<input type="hidden" name="remove_active_instances" value="{remove_active_instances_set}" />
<input type="hidden" name="filter_act_status" value="{filter_act_status_set}">
<input type="hidden" name="show_advanced_actions" value="{show_advanced_actions_set}" />
<input type="hidden" name="iid" value=0 />
<input type="hidden" name="aid" value=0 />
<input type="hidden" name="grab" value=0 />
<input type="hidden" name="release" value=0 />
<input type="hidden" name="run" value=0 />
<input type="hidden" name="send" value=0 />
<input type="hidden" name="exception" value=0 />
<input type="hidden" name="resume" value=0 />
<input type="hidden" name="abort" value=0 />
<script LANGUAGE="JavaScript">
	function submitAnInstanceLine(piid, paid, pfunc) {
		document.userInstancesForm2.iid.value = piid;
		document.userInstancesForm2.aid.value = paid;
		switch (pfunc) {
			case "grab":
				document.userInstancesForm2.grab.value = 1;
				break;
			case "release":
				document.userInstancesForm2.release.value = 1;
				break;
			case "exception":
				document.userInstancesForm2.exception.value = 1;
				break;
			case "resume":
				document.userInstancesForm2.resume.value = 1;
				break;
			case "send":
				document.userInstancesForm2.send.value = 1;
				break;
			case "abort":
				if(confirm("{lang_Confirm_delete}"))
				document.userInstancesForm2.abort.value = 1;
			else
				document.userInstancesForm2.abort.value = 0;
				break;
		}
		document.userInstancesForm2.submit();
	}
</script>
<table style="border: 0;width:100%;">
	<tr class="th" style="font-weight:bold">
		<td>{header_wf_instance_id}</td>
		<td>{header_wf_status}</td>
		<td>{header_wf_priority}</td>
		<td>{header_insname}</td>
		<td>{header_wf_procname}</td>
		<td>{header_wf_name}</td>
		<td>{header_wf_act_status}</td>
		<td>{header_wf_owner}</td>
		<td>{header_wf_user}</td>
		{header_view}
		{lang_Action}</td>
	</tr>
	<!-- BEGIN block_list_instances -->
	<tr bgcolor="{color_line}">
		<td>
		  {instance_id}
		</td>
		<td>
		  {status}
		</td>
		<td {class_priority}>
		  {priority}
		</td>
		<td>
		  {insname}
		</td>
		<td>
		  {wf_procname} {version}
		</td>
		<td>
		  {act_icon} {name}
		</td>
		<td>
		  {act_status}
		</td>
		<td>
		  {owner}
		</td>
		<td>
		  {user}
		</td>
		{column_view}
		
	  		 {run} {send} {grab_or_release} {exception} {resume} {abort} {monitor}
		</td>
	</tr>
	<!-- END block_list_instances -->
	<!-- BEGIN block_filter_instances -->
	<tr>
			<td colspan="2" class="row_off"><div style='text-align:right;'>
				<input size="5" type="text" name="filter_instance" value="{filter_instance_id}"/>
			</div></td>
			<td colspan="9" class="row_on"><div style='text-align:left;'>
				<input type="submit" name="filter" value="{lang_filter_instance_by_id}" />
				<span class="filter_instance_comment">{lang_warning_this_filter_override_all_others_filters}</span>
			</div></td>
	</tr>
	<!-- END block_filter_instances -->
</table>
</form>
