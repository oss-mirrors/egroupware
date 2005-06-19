<div style="color:red; text-align:center">{message}</div>

<form action="{form_action}" method="post">
<input type="hidden" name="start" value="0" />
<input type="hidden" name="sort" value="{sort}" />
<input type="hidden" name="order" value="{order}" />
<table style="border: 1px solid black;width:100%;" cellspacing="0">
	<tr class="th">
		<td colspan="7" style="font-size: 120%; font-weight:bold; border-bottom:3px solid white;">
			{lang_List_of_workitems}
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
			{lang_Instance}
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
			<select name="filter_process">
			<option {filter_process_selected_all} value="">{lang_All}</option>
			<!-- BEGIN block_filter_process -->
			<option {filter_process_selected} value="{filter_process_value}">{filter_process_name} {filter_process_version}</option>
			<!-- END block_filter_process -->
			</select>
		</td>
		<td align="center">
			<select name="filter_activity">
			<option {filter_activity_selected_all} value="">{lang_All}</option>
			<!-- BEGIN block_filter_activity -->
			<option {filter_activity_selected} value="{filter_activity_value}">{filter_activity_name}</option>
			<!-- END block_filter_activity -->
			</select>
		</td>
		<td align="center">
			<input type="text" name="filter_instance" value="{filter_instance}" size="4" />
		</td>
		<td align="center">
			<select name="filter_user">
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
<input type="hidden" name="sort" value="{sort}" />
<input type="hidden" name="order" value="{order}" />
<table style="border: 1px solid black;width:100%;">
	<tr>
		<td colspan="8">
		        <table style="border: 0px;width:100%; margin:0 auto">
				<tr class="th" style="font-weight:bold">
		                	{left}
			        	<td><div align="center">{lang_showing}</div></td>
			                {right}
		        	</tr>
			</table>
		</td>
	</tr>
	<tr class="th" style="font-weight:bold">
		<td>{header_wf_item_id}</td>
		<td>{header_wf_procname}</td>
		<td>{header_wf_act_name}</td>
		<td>{header_wf_instance_id}</td>
		<td>{header_wf_order_id}</td>
		<td>{header_wf_started}</td>
		<td>{header_wf_duration}</td>
		<td>{header_wf_user}</td>
	</tr>
	<!-- BEGIN block_workitems_table -->
	<tr bgcolor="{color_line}">
		<td style="text-align:left;">  
			<a href="{wi_href}">{wi_id}</a>
		</td>
		<td style="text-align:left;">  
			{wi_wf_procname} {wi_version}
		</td>
		<td style="text-align:left;">
			{act_icon} {wi_actname} 
		</td>
		<td>
		  <a href="{wi_adm_inst_href}">{wi_inst_id}</a>
		</td>
		<td>
		  {wi_order_id}
		</td>
		<td>
		  {wi_started}
		</td>
		<td>
		  {wi_duration}
		</td>

		<td>
		  {wi_user}
		</td>
	</tr>
	<!-- END block_workitems_table -->
</table>
</form>
{monitor_stats}
