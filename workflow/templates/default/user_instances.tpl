<div style="color:red; text-align:center">{message}</div>

<form action="{form_action}" method="post">
<input type="hidden" name="offset" value="{start}" />
<input type="hidden" name="sort_mode" value="{sort_mode}" />
<table style="border: 1px solid black;width:100%;">
	<tr class="th">
		<td colspan="6" style="font-size: 120%; font-weight:bold">
			{lang_List_of_processes}
		</td>
	</tr>
	<tr class="th">
		<td >
			{lang_Inst_status}
		</td>
		<td >
			{lang_Process}
		</td>
		<td >
			{lang_User}
		</td>
		<td >
			{lang_Act_status}
		</td>
		<td>
			{lang_Search}
		</td>
		<td >
			&nbsp;
		</td>	
	</tr>
	<tr class="th">
		<td >
			<select name="filter_status">
				<option {selected_filter_status_all} value="">{lang_All}</option>
				<option {selected_filter_status_active} value="active">{lang_Active}</option>
				<option {selected_filter_status_aborted} value="aborted">{lang_Aborted}</option>
				<option {selected_filter_status_exception} value="exception">{lang_Exception}</option>
			</select>
		</td>
		<td >
			<select name="filter_process">
				<option {selected_filter_process_all} value="">{lang_All}</option>
				<!-- BEGIN block_select_process -->
				<option {selected_filter_process} value="{filter_process_id}">{filter_process_name} {filter_process_version}</option>
				<!-- END block_select_process -->
			</select>
		</td>
		<td >
			<select name="filter_user">
				<option {filter_user_all} value="">{lang_All}</option>
				<option {filter_user_star} value="*">*</option>
				<option {filter_user_user} value="{filter_user_id}">{filter_user_name}</option>
			</select>
		</td>
		<td >
			<select name="filter_act_status">
				<option {filter_act_status_all} value="">{lang_All}</option>
				<option value="running" {filter_act_status_running}>{lang_running}</option>
				<option value="completed" {filter_act_status_completed}>{lang_completed}</option>
			</select>
		</td>
		<td >
			<input size="18" type="text" name="search_str" value="{search_str}" />
		</td>
		<td >	
			<input type="submit" name="filter" value="{lang_filter}" />
		</td>
	</tr>
</table>	
</form>

<form action="{form_action}" method="post">
<input type="hidden" name="offset" value="{start}" />
<input type="hidden" name="search_str" value="{search_str}" />
<input type="hidden" name="where" value="{where}" />
<input type="hidden" name="sort_mode" value="{sort_mode}" />
<table style="border: 1px solid black;width:100%;">
	<tr class="th" style="font-weight:bold">
		<td>{header_id}</td>
		<td>{header_owner}</td>
		<td>{header_inst_status}</td>
		<td>{header_process}</td>
		<td>{header_activity}</td>
		<td>{header_user}</td>
		<td>{header_act_status}</td>
		<td>{lang_Action}</td>
	</tr>
	<!-- BEGIN block_list_instances -->
	<tr bgcolor="{color_line}">
		<td>
		  {instanceId}
		</td>
		<td>
		  {owner}
		</td>
		<td>
		  {status}
		</td>
		<td>
		  {procname} {version}
		</td>
		<td>
		  {act_icon} {name}
		</td>
		<td>
		  {user}
		</td>
		<td>
		  {actstatus}
		</td>
		<td>
			  {exception} {send} {run} {abort} {grab_or_release}
		</td>
	</tr>
	<!-- END block_list_instances -->
</table>
</form>
