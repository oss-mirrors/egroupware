<div style="color:red; text-align:center">{message}</div>

<form action="{form_action}" method="post">
<input type="hidden" name="start" value="{start}" />
<input type="hidden" name="sort" value="{sort}" />
<input type="hidden" name="order" value="{order}" />
<table style="border: 1px solid black;width:100%;" cellspacing="0">
	<tr class="th">
		<td colspan="7" style="font-size: 120%; font-weight:bold; border-bottom:3px solid white;">
			{lang_List_of_instances}
		</td>
	</tr>
	<tr class="th">
		<td align="center">
			{lang_Process}
		</td>
		<td align="center">
			{lang_Activity}
		</td>
		<td align="center">
			{lang_Status}
		</td>
		<td align="center">
			{lang_User}
		</td>
		<td align="center">
			{lang_Search}
		</td>
		<td >
			&nbsp;
		</td>	
	</tr>
	<tr class="th">
		<td align="center">
			<select name="filter_process" onchange='this.form.submit();'>
			<option {filter_process_selected_all} value="">{lang_All}</option>
			<!-- BEGIN block_filter_process -->
			<option {filter_process_selected} value="{filter_process_value}">{filter_process_name} {filter_process_version}</option>
			<!-- END block_filter_process -->
			</select>
		</td>
		<td align="center"> 
			<select name="filter_activity" onchange='this.form.submit();'>
			<option {filter_activity_selected_all} value="">{lang_All}</option>
			<!-- BEGIN block_filter_activity -->
			<option {filter_activity_selected} value="{filter_activity_value}">{filter_activity_name}</option>
			<!-- END block_filter_activity -->
			</select>
		</td>
		<td align="center">
			<select name="filter_status" onchange='this.form.submit();'>
			<option {filter_status_selected_all} value="">{lang_All}</option>
			<!-- BEGIN block_filter_status -->
			<option {filter_status_selected} value="{filter_status_value}">{filter_status_name}</option>
			<!-- END block_filter_status -->
			</select>
		</td>
		<td align="center">
			<select name="filter_user" onchange='this.form.submit();'>
			<option {filter_user_selected_all} value="">{lang_All}</option>
			<!-- BEGIN block_filter_user -->
			<option {filter_user_selected} value="{filter_user_value}">{filter_user_name}</option>
			<!-- END block_filter_user -->
			</select>
		</td>
		<td align="center">
			<input size="8" type="text" name="search_str" value="{search_str}" />
		</td>
		<td align="center">	
			<input type="submit" name="filter" value="{lang_filter}" />
		</td>
	</tr>
</table>	
</form>

<form action="{form_action}" method="post">
<input type="hidden" name="start" value="{start}" />
<input type="hidden" name="search_str" value="{search_str}" />
<input type="hidden" name="where" value="{where}" />
<input type="hidden" name="sort" value="{sort}" />
<input type="hidden" name="order" value="{order}" />
<table style="border: 1px solid black;width:100%;">
	<tr class="th" style="font-weight:bold">
		<td>{header_id}</td>
		<td>{header_activity}</td>
		<td>{header_status}</td>
		<td>{header_user}</td>
	</tr>
	<!-- BEGIN block_inst_table -->
	<tr bgcolor="{color_line}">
		<td>
		  <a href="{inst_id_href}">{inst_id}</a>
		</td>
		<td style="text-align:center;">
			{inst_name}
		</td>
		
		<td style="text-align:center;">
			{inst_status}
		</td>
		<td style="text-align:center;">
			{inst_user}
		</td>
	</tr>
	<!-- END block_inst_table -->
</table>
</form>
{monitor_stats}
