

<!-- BEGIN header -->

<form method="POST" action="{action_url}">
<table border="0" align="center" cellpadding="2" cellspacing="2">
	<tr class="th">
		<td colspan="2">&nbsp;<b>{title}</b></td>
	</tr>

<!-- END header -->

<!-- BEGIN body -->

	<tr class="row_on">
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr class="row_off">
		<td colspan="2">&nbsp;<b>{lang_qmail_ldap_schema} - {lang_attribute_names}</b></td>
	</tr>
	<tr class="row_on">
		<td>{lang_mail_address_}:</td>
		<td>
			<select name="newsettings[mail]">
				<option value="mail"{selected_mail_mail}>mail</option>
				<option value="maillocaladdress"{selected_mail_maillocaladdress}>mailLocalAddress</option>
			</select>
		</td>
	</tr>
	<tr class="row_off">
		<td>{lang_forwarding_address}:</td>
        <td>
            <select name="newsettings[routing]">
                <option value="mailforwardingaddress"{selected_routing_mailforwardingaddress}>mailForwardingAddress</option>
                <option value="mailroutingaddress"{selected_routing_mailroutingaddress}>mailRoutingAddress</option>
            </select>
        </td>
	</tr>
	<tr class="row_off">
		<td colspan="2">&nbsp;</td>
	</tr>

<!-- END body -->

<!-- BEGIN footer -->

	<tr class="th">
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" name="submit" value="{lang_submit}">
			<input type="submit" name="cancel" value="{lang_cancel}">
		</td>
	</tr>
</table>
</form>

<!-- END footer -->
