<div style="color:red; text-align:center">{message}</div>

<form id="filterf" action="{form_action}" method="post">
<input type="hidden" name="start" value="{start}" />
<input type="hidden" name="sort" value="{sort}" />
<input type="hidden" name="order" value="{order}" />
<table style="border: 1px solid black;width:100%;" cellspacing="0">
	<tr class="th">
		<td colspan="7" style="font-size: 120%; font-weight:bold; border-bottom:3px solid white;">
			{lang_List_of_activities}
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
			{lang_Type}
		</td>
		<td align="center">
			{lang_Interactive}
		</td>
		<td align="center">
			{lang_Routing}
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
			<select name="filter_type">
				<option {filter_type_selected_all} value="">{lang_All}</option>
				<!-- BEGIN block_filter_type -->
				<option {filter_type_selected} value="{filter_types}">{filter_type}</option>
				<!-- END block_filter_type -->
			</select>
		</td>
		<td align="center">
			<select name="filter_isInteractive">
				<option {filter_interac_selected_all} value="">{lang_All}</option>
				<option value="y" {filter_interac_selected_y}>{lang_Interactive}</option>
				<option value="n" {filter_interac_selected_n}>{lang_Automatic}</option>
			</select>
		</td>
		<td align="center">
			<select name="filter_isAutoRouted">
				<option {filter_route_selected_all} value="">{lang_All}</option>
				<option value="y" {filter_route_selected_y}>{lang_Manual}</option>
				<option value="n" {filter_route_selected_n}>{lang_Automatic}</option>
			</select>
		</td>
		<td align="center">
			<input size="8" type="text" name="search_str" value="{search_str}" />
		</td>
		<td align="center">	
			<input type="submit" name="filter" value="{lang_Filter}" />
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
		<td>&nbsp;</td>
		<td>{header_name}</td>
		<td>{header_type}</td>
		<td align="center">{header_int}</td>
		<td align="center">{header_routing}</td>
		<td align="center">{lang_Instances}</td>
	</tr>
	<!-- BEGIN block_act_table -->
	<tr bgcolor="{color_line}">
		<td style="text-align:center;">
			{act_icon}
		</td>
		<td>
		  <a href="{act_href}">{act_name}</a> {act_run}
		</td>
		<td style="text-align:left;">
			{act_type}
		</td>
		
		<td style="text-align:center;">
			{act_isInteractive}
		</td>
		<td style="text-align:center;">
			{act_isAutorouted}
		</td>
		
		<td style="text-align:right;">
			<table width="100%">
			<tr>
				 <td style="text-align:right;"><a style="color:green;" href="{act_active_href}">{active_instances}</a></td>
				 <td style="text-align:right;"><a style="color:black;" href="{act_completed_href}">{completed_instances}</a></td>
				 <td style="text-align:right;"><a style="color:grey;" href="{act_aborted_href}">{aborted_instances}</a></td>
				 <td style="text-align:right;"><a style="color:red;" href="{act_exception_href}">{exception_instances}</a></td>
			</tr>
			</table>
		</td>
	</tr>
	<!-- END block_act_table -->
</table>
</form>
{monitor_stats}
