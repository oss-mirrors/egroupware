<table style="border: 1px solid black;width:100%;">
	<tr class="th">
		<td colspan="3" style="font-size: 120%; font-weight:bold">
			{lang_List_of_processes}
		</td>
	</tr>
	<tr class="th">
		<td>{lang_Process}</td>
		<td>{lang_Activities}</td>
		<td>{lang_Instances}</td>
	</tr>
	<!-- BEGIN block_table -->
	<tr bgcolor="{color_line}">
		<td>
		  <a href="{link_procname}">{item_procname} {item_version}</a>
		</td>
		<td style="text-align:right;">
			<a  href="{link_activities}">{item_activities}</a>
		</td>
		<td style="text-align:right;">
			<a  href="{link_instances}">{item_instances}</a>
		</td>
	</tr>
	<!-- END block_table -->
</table>
