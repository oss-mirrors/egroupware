

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
		<td><input name="newsettings[mail]" value="{value_mail}"></td>
	</tr>
	<tr class="row_off">
		<td>{lang_forwarding_address}:</td>
		<td><input name="newsettings[routing]" value="{value_routing}"></td>
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
