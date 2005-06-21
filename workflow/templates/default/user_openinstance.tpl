<div style="color:red; text-align:center">{message}</div>

<form action="{form_action}" method="post">
<input type="hidden" name="start" value="{start}" />
<input type="hidden" name="search_str" value="{search_str}" />
<input type="hidden" name="sort" value="{sort}" />
<input type="hidden" name="order" value="{order}" />
<table style="border: 1px solid black;width:100%;">
	<tr class="th">
		<td colspan="2" style="font-size: 120%; font-weight:bold">
			{lang_New_Instance}
		</td>
	</tr>
	<tr class="row_on">
		<td colspan="2">
			{help_info}
		</td>
	</tr>
	<tr><td colspan="2">
        <table style="border: 0px;width:100%; margin:0 auto">
		<tr class="th" style="font-weight:bold">
                	{left}
	        	<td><div align="center">{lang_showing}</div></td>
	                {right}
        	</tr>
	</table>
	</td></tr>
	<tr class="th">
		<td>{header_procname}</td>
		<td>{header_wf_name}</td>
	</tr>
	<!-- BEGIN block_table -->
	<tr bgcolor="{color_line}">
		<td>
		  {procname}
		</td>
		<td>
		  <a href="{link_starting}">{actname}</a>{arrow}
		</td>

	</tr>
	<!-- END block_table -->
</table>
</form>