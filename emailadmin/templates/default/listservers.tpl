<!-- BEGIN main -->
<center>
<b>{lang_server_list}</b>
<br><br>
<table width="90%" border="0" cellspacing="1" cellpading="1">
<tr bgcolor="{th_bg}">
	<td align="center">
		{lang_server_name}
	</td>
	<td align="center">
		{lang_server_description}
	</td>
	<td align="center">
		{lang_edit}
	</td>
	<td align="center">
		{lang_delete}
	</td>
</tr>
{rows}
</table>

</center>
<!-- END main -->

<!-- BEGIN row -->
<tr bgcolor="{row_color}">
	<td align="center">
		{server_name}
	</td>
	<td align="center">
		{server_description}
	</td>
	<td align="center">
		<a href="{edit_link}">{lang_edit}</a>
	</td>
	<td align="center">
		<a href="{delete_link}">{lang_delete}</a>
	</td>
</tr>
<!-- END row -->