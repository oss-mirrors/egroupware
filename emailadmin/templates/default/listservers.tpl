<!-- BEGIN main -->
<center>
<table width="90%" border="0" cellspacing="1" cellpading="1">
<tr>
	<td width="33%">
		&nbsp;
	</td>
	<td width="33%" align="center">
<b>{lang_server_list}</b>
	</td>
	<td width="33%" align="right">
		<a href="{add_link}">{lang_add_server}</a>
	</td>
</tr>
</table>
<br>
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
<tr>
	<td colspan="4">
		&nbsp;
	</td>
</tr>
</table>
</center>
<!-- END main -->

<!-- BEGIN row -->
<tr bgcolor="{row_color}">
	<td align="center">
		<a href="{edit_link}">{server_name}</a>
	</td>
	<td align="center">
		{server_description}
	</td>
	<td align="center">
		<a href="{settings_link}">{lang_edit}</a>
	</td>
	<td align="center">
		<a href="{delete_link}">{lang_delete}</a>
	</td>
</tr>
<!-- END row -->