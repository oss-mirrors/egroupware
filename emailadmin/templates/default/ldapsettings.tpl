<!-- BEGIN main -->

<center>
<table>
<tr>
	<td>
		<form action="{form_action}" method="post">
		<table cellspacing="2" cellpading="2">
		<tr class="row_on">
			<td>
				{lang_server_name}
			</td>
			<td>
				<input type="text" size="50" name="values[qmail_servername]" value="{qmail_servername}">
			</td>
		</tr>
		<tr class="row_off">
			<td>
				{lang_server_description}
			</td>
			<td>
				<input type="text" size="50" name="values[description]" value="{description}">
			</td>
		</tr>
		<tr class="row_on">
			<td>
				{lang_ldap_server}
			</td>
			<td>
				<input type="text" size="50">
			</td>
		</tr>
		<tr class="row_off">
			<td>
				{lang_qmail_base}
			</td>
			<td>
				<input type="text" size="50" name="values[ldap_basedn]" value="{ldap_basedn}">
			</td>
		</tr>
		<tr class="row_on">
			<td>
				{lang_ldap_server_admin}
			</td>
			<td>
				<input type="text" size="50">
			</td>
		</tr>
		<tr class="row_off">
			<td>
				{lang_ldap_server_password}
			</td>
			<td>
				<input type="text" size="50">
			</td>
		</tr>
		<tr class="row_on">
			<td>
				<a href="{done_link}">{lang_back}</a>
			</td>
			<td align="right">
				<input type="submit" name="save_ldap" value="{lang_save}">
				<input type="hidden" name="values[bo_action]" value="save_ldap">
			</td>
		</tr>
		</table>
		</form>
	</td>
</tr>
</table>
</center>
<!-- END main -->

<!-- BEGIN menu_row -->
<tr class="th">
	<td>
		<nobr><a href="{menu_link}">{menu_description}</a><nobr>
	</td>
</tr>
<!-- END menu_row -->

<!-- BEGIN menu_row_bold -->
<tr class="th">
	<td>
		<nobr><b><a href="{menu_link}">{menu_description}</a></b><nobr>
	</td>
</tr>
<!-- END menu_row_bold -->
