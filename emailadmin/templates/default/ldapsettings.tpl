<!-- BEGIN main -->
<center>
<table border="0" cellspacing="1" cellpading="0" width="95%">
<tr>
	<td width="10%" valign="top">
		<table border="0" cellspacing="1" cellpading="0" width="100%">
		{menu_rows}
		<tr>
			<td>
				&nbsp;
			</td>
		</tr>
		<tr bgcolor="{done_row_color}">
			<td>
				<a href="{done_link}">Done</a>
			</td>
		</tr>
		</table>
	</td>
	<td width="90%" valign="top">
		<table border="0" cellspacing="1" cellpading="0" width="100%">
		<tr bgcolor="{bg_01}">
			<td>
				{lang_server_name}
			</td>
			<td>
				<input type="text" size="50">
			</td>
		</tr>
		<tr bgcolor="{bg_02}">
			<td>
				{lang_server_description}
			</td>
			<td>
				<input type="text" size="50">
			</td>
		</tr>
		<tr bgcolor="{bg_01}">
			<td>
				{lang_ldap_server}
			</td>
			<td>
				<input type="text" size="50">
			</td>
		</tr>
		<tr bgcolor="{bg_02}">
			<td>
				{lang_ldap_server_admin}
			</td>
			<td>
				<input type="text" size="50">
			</td>
		</tr>
		<tr bgcolor="{bg_01}">
			<td>
				{lang_ldap_server_password}
			</td>
			<td>
				<input type="text" size="50">
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>
</center>
<!-- END main -->

<!-- BEGIN menu_row -->
<tr bgcolor="{menu_row_color}">
	<td>
		<nobr><a href="{menu_link}">{menu_description}</a><nobr>
	</td>
</tr>
<!-- END menu_row -->

<!-- BEGIN menu_row_bold -->
<tr bgcolor="{menu_row_color}">
	<td>
		<nobr><b><a href="{menu_link}">{menu_description}</a></b><nobr>
	</td>
</tr>
<!-- END menu_row_bold -->
