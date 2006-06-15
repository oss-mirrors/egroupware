<!-- BEGIN main -->
<center>
<form action="{form_action}" name="editAccountData" method="post">
<div style="width:650px; text-align:left;">
<input type="checkbox" id="active" name="active" value="1" onchange="onchange_active(this)" {checked_active}>{lang_use_costum_settings}
</div>
<fieldset style="width:650px;" class="row_on" id="identity"><legend style="font-weight: bold;">{lang_identity}</legend>
<table width="100%" border="0" cellpadding="0" cellspacing="1">
	<tr>
		<td style="width: 300px;">
			{lang_name}
		</td>
		<td>
			<input type="text" style="width: 100%;" name="identity[realName]" value="{identity[realName]}" maxlength="128">
		</td>
	</tr>
	<tr>
		<td style="width: 300px;">
			{lang_organization}
		</td>
		<td>
			<input type="text" style="width: 100%;" name="identity[organization]" value="{identity[organization]}" maxlength="128">
		</td>
	</tr>
	<tr>
		<td>
			{lang_emailaddress}
		</td>
		<td>
			<input type="text" style="width: 100%;" name="identity[emailAddress]" value="{identity[emailAddress]}" maxlength="128">
		</td>
	</tr>
</table>
</fieldset>

<fieldset style="width:650px;" class="row_on" id="incoming_server"><legend style="font-weight: bold;">{lang_incoming_server}</legend>
<table width="100%" border="0" cellpadding="0" cellspacing="1">
	<tr>
		<td style="width: 300px;">
			{hostname_address}
		</td>
		<td>
			<input type="text" style="width: 100%;" name="ic[host]" value="{ic[host]}" maxlength="128">
		</td>
	</tr>
	<tr>
		<td style="width: 300px;">
			{lang_port}
		</td>
		<td>
			<input type="text" style="width: 5em;" id="ic[port]" name="ic[port]" value="{ic[port]}" maxlength="5">
		</td>
	</tr>
	<tr>
		<td>
			{lang_username}
		</td>
		<td>
			<input type="text" style="width: 100%;" name="ic[username]" value="{ic[username]}" maxlength="128">
		</td>
	</tr>
	<tr>
		<td>
			 {lang_password}
		</td>
		<td>
			<input type="text" style="width: 100%;" name="ic[password]" value="{ic[password]}" maxlength="128">
		</td>
	</tr>
	<tr>
		<td>
			 {lang_encrypted_connection}
		</td>
		<td>
			<input type="checkbox" id="ic[encryption]" name="ic[encryption]" value="1" onchange="onchange_ic_encryption(this)" {checked_ic_encryption}>
		</td>
	</tr>
	<tr>
		<td>
			 {lang_validate_certificate}
		</td>
		<td>
			<input type="checkbox" id="ic[validatecert]" name="ic[validatecert]" value="1" {checked_ic_validatecert}>
		</td>
	</tr>
</table>
</fieldset>

<fieldset style="width:650px;" class="row_on" id="outgoing_server"><legend style="font-weight: bold;">{lang_outgoing_server}</legend>
<table width="100%" border="0" cellpadding="0" cellspacing="1">
	<tr>
		<td style="width: 300px;">
			{hostname_address}
		</td>
		<td>
			<input type="text" style="width: 100%;" name="og[host]" value="{og[host]}" maxlength="128">
		</td>
	</tr>
	<tr>
		<td style="width: 300px;">
			{lang_port}
		</td>
		<td>
			<input type="text" style="width: 5em;" id="og[port]" name="og[port]" value="{og[port]}" maxlength="5">
		</td>
	</tr>
	<tr>
		<td>
			 {auth_required}
		</td>
		<td>
			<input type="checkbox" id="og[smtpauth]" name="og[smtpAuth]" value="1" onchange="onchange_og_smtpauth(this)" {checked_og_smtpAuth}>
		</td>
	</tr>
	<tr>
		<td>
			{lang_username}
		</td>
		<td>
			<input type="text" style="width: 100%;" id="og[username]" name="og[username]" value="{og[username]}" maxlength="128">
		</td>
	</tr>
	<tr>
		<td>
			 {lang_password}
		</td>
		<td>
			<input type="text" style="width: 100%;" id="og[password]" name="og[password]" value="{og[password]}" maxlength="128">
		</td>
	</tr>
</table>
</fieldset>

<table width="650px" border="0" cellpadding="0" cellspacing="1">
	<tr>
		<td>
			<input type="submit" name="save" value="{lang_save}">
			<input type="submit" name="apply" value="{lang_apply}">
			<input type="submit" name="cancel" value="{lang_cancel}">
		</td>
	</tr>
</table>
<form>
</center>
<!-- END main -->
