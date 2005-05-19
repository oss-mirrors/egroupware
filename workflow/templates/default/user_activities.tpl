<form action="{form_filtering_action}" method="post" id='fform'>
<input type="hidden" name="start" value="{start}" />
<input type="hidden" name="sort" value="{sort}" />
<input type="hidden" name="order" value="{order}" />
<table style="border: 1px solid black;width:100%;">
	<tr class="th">
		<td colspan="3" style="font-size: 120%; font-weight:bold">
			{lang_List_of_activities}
		</td>
	</tr>
	<tr class="th" style="font-weight:bold">
		<td>
			{header_process}:
				<select onchange='this.form.submit();' name="filter_process">
					<option {filter_process_all_selected} value="">{lang_All}</option>
					<!-- BEGIN block_select_process -->
					<option {filter_process_selected} value="{filter_process_value}">{filter_process_name} {filter_process_version}</option>
					<!-- END block_select_process -->
				</select>
		</td>
		<td>
			{header_activity}
		</td>
		<td>
			{lang_Instances}
		</td>
	</tr>
	<!-- BEGIN block_activities_list -->
	<tr bgcolor="{color_line}">
		<td>
		  {act_wf_procname} {act_proc_version}
		</td>
		<td style="text-align:left;">
			{act_icon} {act_name} {run_act}
		</td>
		<td style="text-align:right;">
			{act_instances}
		</td>
	</tr>
	<!-- END block_activities_list -->
</table>
</form>
