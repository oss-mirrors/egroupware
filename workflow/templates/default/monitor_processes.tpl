<div style="color:red; text-align:center">{message}</div>

<form action="{form_action}" method="post">
<input type="hidden" name="start" value="{start}" />
<input type="hidden" name="sort_mode" value="{sort_mode}" />
<table style="border: 1px solid black;width:70%;margin:0 auto" cellspacing="0">
	<tr class="th">
		<td colspan="5" style="font-size: 120%; font-weight:bold; border-bottom:3px solid white;">
			{lang_List_of_processes}
		</td>
	</tr>
	<tr class="th">
		<td align="center">
			{lang_Process}
		</td>
		<td align="center">
			{lang_Active}
		</td>
		<td align="center">
			{lang_Valid}
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
			<select onchange='this.form.submit();' name="filter_process">
				<option {filter_process_selected_all} value="">{lang_All}</option>
				<!-- BEGIN block_filter_process -->
					<option {filter_process_selected} value="{filter_process_value}">{filter_process_name} {filter_process_version}</option>
				<!-- END block_filter_process -->
			</select>
		</td>
		<td align="center">
			<select onchange='this.form.submit();' name="filter_active">
				<option {selected_active_all} value="">{lang_All}</option>
				<option value="y" {selected_active_active}>{lang_Active}</option>
				<option value="n" {selected_active_inactive}>{lang_Inactive}</option>
			</select>
		</td>
		<td align="center">
			<select onchange='this.form.submit();' name="filter_valid">
				<option {selected_valid_all} value="">{lang_All}</option>
				<option {selected_valid_valid} value="y">{lang_Valid}</option>
				<option {selected_valid_invalid} value="n">{lang_Invalid}</option>
			</select>
		</td>
		<td align="center">
			<input size="18" type="text" name="search_str" />
		</td>
		<td align="center">	
			<input type="submit" name="filter" value="{lang_filter}" />
		</td>
	</tr>
</table>	
</form>

<form action="{form_action}" method="post">
<input type="hidden" name="start" value="{start}" />
<input type="hidden" name="search_str" value="{find}" />
<input type="hidden" name="where" value="{where}" />
<input type="hidden" name="sort_mode" value="{sort_mode}" />
<table style="border: 1px solid black;width:70%;margin:0 auto">
	<tr class="th">
		<td>{header_name}</td>
		<td align="center">{lang_Activities}</td>
		<td align="center" style="width:50px">{header_act}</td>
		<td align="center" style="width:50px">{header_val}</td>
		<td align="center">{lang_Instances}</td>
	</tr>
	<!-- BEGIN block_listing -->
	<tr bgcolor="{color_line}">
		<td>
		  <a href="{process_href}">{process_name} {process_version}</a>
		</td>
		<td style="text-align:right;">
			<a href="{process_href_activities}">{process_activities}</a>
		</td>
		<td style="text-align:center;">
			{process_active_img}
		</td>
		<td style="text-align:center;">
		  <img src='{process_valid_img}' alt=' ({process_valid_alt}) ' title='{process_valid_alt}' />
		</td>
		<td style="text-align:right;">
			<table width="100%">
			<tr>
			 <td style="text-align:right;"><a style="color:green;" href="{process_href_inst_active}">{process_inst_active}</a></td>
			 <td style="text-align:right;"><a style="color:black;" href="{process_href_inst_comp}">{process_inst_comp}</a></td>
			 <td style="text-align:right;"><a style="color:grey;" href="{process_href_isnt_abort}">{process_inst_abort}</a></td>
			 <td style="text-align:right;"><a style="color:red;" href="{process_href_inst_excep}">{process_inst_excep}</a></td>
			</tr>
			</table>
		</td>
	</tr>
	<!-- END block_listing -->
</table>
</form>
{monitor_stats}
