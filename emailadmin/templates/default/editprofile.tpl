<!-- BEGIN main -->
<center>
<form action="{action_url}" name="mailsettings" method="post">
<br>
<table width="670px" border="0" cellspacing="0" cellpading="0">
	<tr>
		<th width="33%" id="tab1" class="activetab" onclick="javascript:tab.display(1);"><a href="#" tabindex="1" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);">Global</a></th>
		<th width="33%" id="tab2" class="activetab" onclick="javascript:tab.display(2);"><a href="#" tabindex="2" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); return(false);">SMTP</a></th>
		<th width="33%" id="tab3" class="activetab" onclick="javascript:tab.display(3);"><a href="#" tabindex="3" accesskey="3" onfocus="tab.display(3);" onclick="tab.display(3); return(false);">POP3/IMAP</a></th>
<!--		<th id="tab4" class="activetab" onclick="javascript:tab.display(4);"><a href="#" tabindex="4" accesskey="4" onfocus="tab.display(4);" onclick="tab.display(4); return(false);">extern</a></th> -->
	</tr>
</table>
<br><br>


<!-- The code for Global Tab -->

<div id="tabcontent1" class="inactivetab">
	<table width="670px" border="0" cellspacing="0" cellpadding="5">
		<tr class="th">
			<td width="300px">
				<b>{lang_profile_name}</b>
			</td>
			<td align="right">
				<input style="width: 250px;" type="text" size="30" name="globalsettings[description]" value="{value_description}">
			</td>
		</tr>
	</table>
	<p>
	<fieldset style="width:650px;" class="row_on"><legend>{lang_organisation}</legend>
	<table width="100%" border="0" cellspacing="0" cellpading="1">
		<tr>
			<td width="300px">
				{lang_default_domain}:
			</td>
			<td>
				<input style='width: 350px;' type="text" size="30" name="globalsettings[defaultDomain]" value="{value_defaultDomain}">
			</td>
		</tr>
		<tr>
			<td>
				{lang_organisation_name}:
			</td>
			<td>
				<input style='width: 350px;' type="text" size="30" name="globalsettings[organisationName]" value="{value_organisationName}">
			</td>
		</tr>
	</table>
	</fieldset>
	<p>
	<fieldset style="width:650px;" class="row_off"><legend>{lang_profile_access_rights}</legend>
	<table width="100%" border="0" cellspacing="0" cellpading="1">
		<tr>
			<td width="300px">
				{lang_can_be_used_by_application}:
			</td>
			<td>
				{application_select_box}
			</td>
		</tr>
		<tr>
			<td>
				{lang_can_be_used_by_group}:
			</td>
			<td>
				{group_select_box}
			</td>
		</tr>
	</table>
	</fieldset>
	<p>
	<fieldset style="width:650px;" class="row_off"><legend>{lang_global_options}</legend>
	<table width="100%" border="0" cellspacing="0" cellpading="1">
		<tr>
			<td width="300px">
				{lang_user_defined_accounts}:
			</td>
			<td>
				<input type="checkbox" name="globalsettings[userDefinedAccounts]" {selected_userDefinedAccounts} value="yes">
			</td>
		</tr>
	</table>
	</fieldset>
</div>


<!-- The code for SMTP Tab -->

<div id="tabcontent2" class="inactivetab">
	<table width="670px" border="0" cellspacing="0" cellpadding="5">
		<tr class="th">
			<td width="50%" cclass="td_left">
				<b>{lang_Select_type_of_SMTP_Server}<b>
			</td>
			<td width="50%" align="right" cclass="td_right">
				<select  style="width: 250px;" name="smtpsettings[smtpType]" id="smtpselector" size="1" onchange="javascript:smtp.display(this.value);">
					<option value="1" {selected_smtpType_1}>{lang_smtp_option_1}</option>
					<option value="2" {selected_smtpType_2}>{lang_smtp_option_2}</option>
				</select>
			</td>
		</tr>
	</table>
	<p>
	
	<!-- The code for standard SMTP Server -->
	
	<div id="smtpcontent1" class="inactivetab">
		<fieldset style="width:650px;" class="row_on"><legend>{lang_smtp_settings}</legend>
		<table width="100%" border="0" cellspacing="0" cellpading="1">
			<tr>
				<td width="300px">{lang_SMTP_server_hostname_or_IP_address}:</td>
				<td><input name="smtpsettings[1][smtpServer]" size="40" value="{value_smtpServer}"></td>
			</tr>
			
			<tr class="row_on">
				<td>{lang_SMTP_server_port}:</td>
				<td><input name="smtpsettings[1][smtpPort]" maxlength="5" size="5" value="{value_smtpPort}"></td>
			</tr>
		</table>
		</fieldset>
		<p>
		<fieldset style="width:650px;"><legend>{lang_smtp_auth}</legend>
		<table width="100%" border="0" cellspacing="0" cellpading="1">
			<tr class="row_off">
				<td width="300px">{lang_Use_SMTP_auth}:</td>
				<td>
					<input type="checkbox" name="smtpsettings[1][smtpAuth]" {selected_smtpAuth} value="yes">
				</td>
			</tr>
			<tr class="row_off">
				<td>{lang_username}:</td>
				<td>
					<input type="text" name="smtpsettings[1][smtpauthusername]" style="width: 350px;" value="{value_smtpauthusername}">
				</td>
			</tr>
			<tr class="row_off">
				<td>{lang_password}:</td>
				<td>
					<input type="password" name="smtpsettings[1][smtpauthpassword]" style="width: 350px;" value="{value_smtpauthpassword}">
				</td>
			</tr>
		</table>
		</fieldset>
	</div>
	
	
	<!-- The code for Postfix/LDAP Server -->
	
	<div id="smtpcontent2" class="inactivetab">
		<fieldset style="width:650px;" class="row_on"><legend>{lang_smtp_settings}</legend>
		<table width="100%" border="0" cellspacing="0" cellpading="1">
			<tr>
				<td width="300px">{lang_SMTP_server_hostname_or_IP_address}:</td>
				<td><input name="smtpsettings[2][smtpServer]" size="40" value="{value_smtpServer}"></td>
			</tr>
			
			<tr>
				<td>{lang_SMTP_server_port}:</td>
				<td><input name="smtpsettings[2][smtpPort]" maxlength="5" size="5" value="{value_smtpPort}"></td>
			</tr>
		</table>
		</fieldset>
		<p>
		<fieldset style="width:650px;"><legend>{lang_smtp_auth}</legend>
		<table width="100%" border="0" cellspacing="0" cellpading="1">
			<tr class="row_off">
				<td width="300px">{lang_Use_SMTP_auth}:</td>
				<td>
					<input type="checkbox" name="smtpsettings[1][smtpAuth]" {selected_smtpAuth} value="yes">
				</td>
			</tr>
			<tr class="row_off">
				<td>{lang_username}:</td>
				<td>
					<input type="text" name="smtpsettings[1][smtpauthusername]" style="width: 350px;" value="{value_smtpauthusername}">
				</td>
			</tr>
			<tr class="row_off">
				<td>{lang_password}:</td>
				<td>
					<input type="password" name="smtpsettings[1][smtpauthpassword]" style="width: 350px;" value="{value_smtpauthpassword}">
				</td>
			</tr>
		</table>
		</fieldset>
		<p>
		<fieldset style="width:650px;"><legend>{lang_smtp_options}</legend>
		<table width="100%" border="0" cellspacing="0" cellpading="1">
			<tr>
				<td width="300px">{lang_user_can_edit_forwarding_address}:</td>
				<td>
					<input type="checkbox" name="smtpsettings[2][editforwardingaddress]" {selected_editforwardingaddress} value="yes">
				</td>
			</tr>
		</table>
		</fieldset>
<!--		<table>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
		</table>
		<table width="90%" border="0" cellspacing="0" cellpading="1">
			<tr class="th">
				<td width="50%" class="td_left">
					<b>{lang_LDAP_settings}<b>
				</td>
				<td class="td_right">
					&nbsp;
				</td>
			</tr>
			<tr class="row_off">
				<td class="td_left">{lang_use_LDAP_defaults}:</td>
				<td class="td_right">
					<input type="checkbox" name="smtpsettings[2][smtpLDAPUseDefault]" {selected_smtpLDAPUseDefault} value="yes">
				</td>
			</tr>
			<tr class="row_on">
				<td width="50%" class="td_left">{lang_LDAP_server_hostname_or_IP_address}:</td>
				<td width="50%" class="td_right"><input name="smtpsettings[2][smtpLDAPServer]" maxlength="80" size="40" value="{value_smtpLDAPServer}"></td>
			</tr>
			
			<tr class="row_off">
				<td class="td_left">{lang_LDAP_server_admin_dn}:</td>
				<td class="td_right"><input name="smtpsettings[2][smtpLDAPAdminDN]" maxlength="200" size="40" value="{value_smtpLDAPAdminDN}"></td>
			</tr>
			
			<tr class="row_on">
				<td class="td_left">{lang_LDAP_server_admin_pw}:</td>
				<td class="td_right"><input type="password" name="smtpsettings[2][smtpLDAPAdminPW]" maxlength="30" size="40" value="{value_smtpLDAPAdminPW}"></td>
			</tr>

			<tr class="row_off">
				<td class="td_left">{lang_LDAP_server_base_dn}:</td>
				<td class="td_right"><input name="smtpsettings[2][smtpLDAPBaseDN]" maxlength="200" size="40" value="{value_smtpLDAPBaseDN}"></td>
			</tr>
		</table> -->
	</div>
</div>


<!-- The code for IMAP/POP3 Tab -->

<div id="tabcontent3" class="inactivetab">
	<table width="670px" border="0" cellspacing="0" cellpadding="5">
		<tr class="th">
			<td width="50%">
				<b>{lang_select_type_of_imap/pop3_server}</b>
			</td>
			<td width="50%" align="right">
				<select  style="width: 250px;" name="imapsettings[imapType]" id="imapselector" size="1" onchange="javascript:imap.display(this.value);">
					<option value="1" {selected_imapType_1}>{lang_imap_option_1}</option>
					<option value="2" {selected_imapType_2}>{lang_imap_option_2}</option>
					<option value="3" {selected_imapType_3}>{lang_imap_option_3}</option>
				</select>
			</td>
		</tr>
	</table>
	<p>

	<!-- The code for standard POP3 Server -->
	
	<div id="imapcontent1" class="inactivetab">
		<fieldset style="width:650px;" class="row_on"><legend>{lang_server_settings}</legend>
		<table width="100%" border="0" cellspacing="0" cellpading="1">
			<tr>
				<td width="300px">{lang_pop3_server_hostname_or_IP_address}:</td>
				<td><input name="imapsettings[1][imapServer]" maxlength="80" style="width: 350px;" value="{value_imapServer}"></td>
			</tr>
			
			<tr>
				<td>{lang_pop3_server_port}:</td>
				<td><input name="imapsettings[1][imapPort]" maxlength="5" size="5" value="{value_imapPort}"></td>
			</tr>
			
			<tr>
				<td>{lang_imap_server_logintyp}:</td>
				<td>
					<select name="imapsettings[1][imapLoginType]" style="width: 350px;" size="1">
						<option value="standard" {selected_imapLoginType_standard}>{lang_standard}</option>
						<option value="vmailmgr" {selected_imapLoginType_vmailmgr}>{lang_vmailmgr}</option>
					</select>
				</td>

			</tr>
		</table>
		</fieldset>
		<p>
		<fieldset style="width:650px;"><legend>{lang_encryption_settings}</legend>
                <table width="100%" border="0" cellspacing="0" cellpading="1">

			<tr>
				<td width="300px">{lang_use_tls_encryption}:</td>
				<td>
					<input type="checkbox" name="imapsettings[1][imapTLSEncryption]" {selected_imapTLSEncryption} value="yes">
				</td>
			</tr>

			<tr>
				<td>{lang_use_tls_encryption}:</td>
				<td>
					<input type="checkbox" name="imapsettings[1][imapTLSEncryption]" {selected_imapTLSEncryption} value="yes">
				</td>
			</tr>

			<tr>
				<td>{lang_pre_2001_c_client}:</td>
				<td>
					<input type="checkbox" name="imapsettings[1][imapoldcclient]" {selected_imapoldcclient} value="yes">
				</td>
			</tr>
		</table>
		</fieldset>
	</div>
	

	<!-- The code for standard IMAP Server -->
	
	<div id="imapcontent2" class="inactivetab">
		<fieldset style="width:650px;" class="row_on"><legend>{lang_server_settings}</legend>
		<table width="100%" border="0" cellspacing="0" cellpading="1">
			<tr>
				<td width="300px">{lang_imap_server_hostname_or_IP_address}:</td>
				<td><input name="imapsettings[2][imapServer]" maxlength="80" style="width: 350px;" value="{value_imapServer}"></td>
			</tr>
			
			<tr>
				<td>{lang_imap_server_port}:</td>
				<td><input name="imapsettings[2][imapPort]" maxlength="5" size="5" value="{value_imapPort}"></td>
			</tr>
			
			<tr>
				<td>{lang_imap_server_logintyp}:</td>
				<td>
					<select name="imapsettings[2][imapLoginType]" style="width: 350px;" size="1">
						<option value="standard" {selected_imapLoginType_standard}>{lang_standard}</option>
						<option value="vmailmgr" {selected_imapLoginType_vmailmgr}>{lang_vmailmgr}</option>
					</select>
				</td>

			</tr>
		</table>
		</fieldset>
		<p>
		<fieldset style="width:650px;"><legend>{lang_encryption_settings}</legend>
                <table width="100%" border="0" cellspacing="0" cellpading="1">

			<tr>
				<td width="300px">{lang_use_tls_encryption}:</td>
				<td>
					<input type="checkbox" name="imapsettings[2][imapTLSEncryption]" {selected_imapTLSEncryption} value="yes">
				</td>
			</tr>

			<tr>
				<td>{lang_use_tls_authentication}:</td>
				<td>
					<input type="checkbox" name="imapsettings[2][imapTLSAuthentication]" {selected_imapTLSAuthentication} value="yes">
				</td>
			</tr>

			<tr>
				<td>{lang_pre_2001_c_client}:</td>
				<td>
					<input type="checkbox" name="imapsettings[2][imapoldcclient]" {selected_imapoldcclient} value="yes">
				</td>
			</tr>
		</table>
		</fieldset>
	</div>
	

	<!-- The code for the Cyrus IMAP Server -->
	
	<div id="imapcontent3" class="inactivetab">
		<fieldset style="width:650px;" class="row_on"><legend>{lang_server_settings}</legend>
		<table width="100%" border="0" cellspacing="0" cellpading="1">
			<tr>
				<td width="300px">{lang_imap_server_hostname_or_IP_address}:</td>
				<td><input name="imapsettings[3][imapServer]" maxlength="80" style="width: 350px;" value="{value_imapServer}"></td>
			</tr>
			
			<tr>
				<td>{lang_imap_server_port}:</td>
				<td><input name="imapsettings[3][imapPort]" maxlength="5" size="5" value="{value_imapPort}"></td>
			</tr>
			
			<tr>
				<td>{lang_imap_server_logintyp}:</td>
				<td>
					<select name="imapsettings[3][imapLoginType]" style="width: 350px;" size="1">
						<option value="standard" {selected_imapLoginType_standard}>{lang_standard}</option>
						<option value="vmailmgr" {selected_imapLoginType_vmailmgr}>{lang_vmailmgr}</option>
					</select>
				</td>

			</tr>
		</table>
		</fieldset>
		<p>
		<fieldset style="width:650px;"><legend>{lang_encryption_settings}</legend>
                <table width="100%" border="0" cellspacing="0" cellpading="1">

			<tr>
				<td width="300px">{lang_use_tls_encryption}:</td>
				<td>
					<input type="checkbox" name="imapsettings[3][imapTLSEncryption]" {selected_imapTLSEncryption} value="yes">
				</td>
			</tr>

			<tr>
				<td>{lang_use_tls_authentication}:</td>
				<td>
					<input type="checkbox" name="imapsettings[3][imapTLSAuthentication]" {selected_imapTLSAuthentication} value="yes">
				</td>
			</tr>

			<tr>
				<td>{lang_pre_2001_c_client}:</td>
				<td>
					<input type="checkbox" name="imapsettings[3][imapoldcclient]" {selected_imapoldcclient} value="yes">
				</td>
			</tr>
		</table>
		</fieldset>
		<p>
		<fieldset style="width:650px;"><legend>{lang_cyrus_imap_administration}</legend>
		<table width="100%" border="0" cellspacing="0" cellpading="1">
			<tr class="row_off">
				<td width="300px">{lang_enable_cyrus_imap_administration}:</td>
				<td>
					<input type="checkbox" name="imapsettings[3][imapEnableCyrusAdmin]" {selected_imapEnableCyrusAdmin} value="yes">
				</td>
			</tr>
			<tr>
				<td>{lang_admin_username}:</td>
				<td><input name="imapsettings[3][imapAdminUsername]" maxlength="40"  style="width: 350px;" value="{value_imapAdminUsername}"></td>
			</tr>

			<tr>
				<td>{lang_admin_password}:</td>
				<td><input type="password" name="imapsettings[3][imapAdminPW]" maxlength="40"  style="width: 350px;" value="{value_imapAdminPW}"></td>
			</tr>
		</table>
		</fieldset>
		<p>
		<fieldset style="width:650px;"><legend>{lang_sieve_settings}</legend>
		<table width="100%" border="0" cellspacing="0" cellpading="1">
			<tr>
				<td width="300px">{lang_enable_sieve}:</td>
				<td>
					<input type="checkbox" name="imapsettings[3][imapEnableSieve]" {selected_imapEnableSieve} value="yes">
				</td>
			</tr>
			<tr>
				<td>{lang_sieve_server_hostname_or_ip_address}:</td>
				<td><input name="imapsettings[3][imapSieveServer]" maxlength="80" style="width: 350px;" value="{value_imapSieveServer}"></td>
			</tr>

			<tr>
				<td>{lang_sieve_server_port}:</td>
				<td><input name="imapsettings[3][imapSievePort]" maxlength="5" size="5" value="{value_imapSievePort}"></td>
			</tr>
		</table>
		</fieldset>
	</div>
	
	
</div>


<!-- The code for External Tab -->

<div id="tabcontent4" class="inactivetab">
	<h1>still something todo ...</h1>
	<p>Come back later!!</p>
</div>


<br><br>
<table width="670px" border="0" cellspacing="0" cellpading="1">
	<tr>
		<td width="90%" align="left"  class="td_left">
			<a href="{back_url}">{lang_back}</a>
		</td>
		<td width="10%" align="center" class="td_right">
			<a href="javascript:document.mailsettings.submit();">{lang_save}</a>
		</td>
	</tr>
</table>
</form>
</center>
<!-- END main -->

