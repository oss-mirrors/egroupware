<!-- $Id$ -->

<!-- BEGIN header -->
 
<form method="post" action="{action_url}">
<table align="center" cellspacing="0" border="5" width="90%" >
   <tr class="th">
    <td colspan="2">&nbsp;{title}</td>
   </tr>

<!-- END header -->

<!-- BEGIN body -->
   <tr class="th">
    <td colspan="2"><b>{lang_Path_information}</b></td>
   </tr>
   
   <tr class="row_off">
    <td>{lang_Enter_the_full_path_for_temporary_files.<br />Examples:_/tmp,_C:\TEMP}:</td>
    <td><input name="newsettings[temp_dir]" value="{value_temp_dir}" size="40" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Enter_the_full_path_for_users_and_group_files.<br />Examples:_/files,_E:\FILES}:<br /><b>{lang_This_has_to_be_outside_the_webservers_document-root!!!}</b><br />{lang_or_http://webdav.domain.com_(WebDAV)}:</td>
    <td><input name="newsettings[files_dir]" value="{value_files_dir}" size="40" /></td>
   </tr>
   
   <tr class="row_off">
    <td>{lang_Enter_the_full_path_to_the_backup_directory.<br />if_empty:_files_directory}/db_backup:<br /><b>{lang_This_has_to_be_outside_the_webservers_document-root!!!}</b></td>
    <td><input name="newsettings[backup_dir]" value="{value_backup_dir}" size="40" /></td>
   </tr>
   
   <tr class="row_on">
    <td>{lang_Enter_the_location_of_eGroupWare's_URL.<br />Example:_http://www.domain.com/egroupware_&nbsp;_or_&nbsp;_/egroupware<br /><b>No_trailing_slash</b>}:</td>
    <td><input name="newsettings[webserver_url]" value="{value_webserver_url}" size="40" /></td>
   </tr>

   <tr class="row_off">
    <td>{lang_Image_type_selection_order}:</td>
    <td>
     <select name="newsettings[image_type]">
      <option value="">GIF->JPG->PNG</option>
      <option value="1"{selected_image_type_1}>PNG->JPG->GIF</option>
      <option value="2"{selected_image_type_2}>PNG->JPG</option>
     </select>
    </td>
   </tr>
   <tr class="row_on">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr class="th">
    <td colspan="2"><b>{lang_Host_information}</b></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Enter_the_hostname_of_the_machine_on_which_this_server_is_running}:</td>
    <td><input name="newsettings[hostname]" value="{value_hostname}" /></td>
   </tr>

   <tr class="row_off">
    <td>{lang_Enter_your_default_FTP_server}:</td>
    <td><input name="newsettings[default_ftp_server]" value="{value_default_ftp_server}" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Attempt_to_use_correct_mimetype_for_FTP_instead_of_default_'application/octet-stream'}:</td>
    <td>
     <select name="newsettings[ftp_use_mime]">
      <option value="">{lang_No}</option>
      <option value="True"{selected_ftp_use_mime_True}>{lang_Yes}</option>
     </select>
    </td>
   </tr>
<!-- this is not working correct
   <tr class="row_off">
    <td>{lang_Datetime_port.<br />If_using_port_13,_please_set_firewall_rules_appropriately_before_submitting_this_page.<br />(Port:_13_/_Host:_129.6.15.28)}</td>
    <td>
      <select name="newsettings[daytime_port]">
       <option value="00"{selected_daytime_port_00}>{lang_00_(disable)}</option>
       <option value="13"{selected_daytime_port_13}>{lang_13_(ntp)}</option>
       <option value="80"{selected_daytime_port_80}>{lang_80_(http)}</option>
      </select>
    </td>
   </tr>
-->
   <tr class="row_off">
    <td>{lang_Enter_your_HTTP_proxy_server}:</td>
    <td><input name="newsettings[httpproxy_server]" value="{value_httpproxy_server}" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Enter_your_HTTP_proxy_server_port}:</td>
    <td><input name="newsettings[httpproxy_port]" value="{value_httpproxy_port}" /></td>
   </tr>

   <tr class="row_off">
    <td>{lang_Enter_your_HTTP_proxy_server_username}:</td>
    <td><input name="newsettings[httpproxy_server_username]" value="{value_httpproxy_server_username}" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Enter_your_HTTP_proxy_server_password}:</td>
    <td><input name="newsettings[httpproxy_server_password]" value="{value_httpproxy_server_password}" /></td>
   </tr>

<!-- until they are working
   <tr class="row_off">
    <td>{lang_Enter_the_site_username_for_peer_servers}.</td>
    <td><input name="newsettings[site_username]" value="{value_site_username}" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Enter_the_site_password_for_peer_servers}.</td>
    <td><input type="password" name="newsettings[site_password]" value="{value_site_password}" /></td>
   </tr>
-->

  <tr class="row_off">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr class="th">
    <td colspan="2"><b>{lang_Standard_mailserver_settings_(used_for_Mail_authentication_too)}:</b></td>
   </tr>
   <tr class="row_on"">
    <td>{lang_POP/IMAP_mail_server_hostname_or_IP_address}:</td>
    <td><input name="newsettings[mail_server]" value="{value_mail_server}"></td>
   </tr>
   <tr class="row_off"">
    <td>{lang_Mail_server_protocol}:</td>
    <td>
     <select name="newsettings[mail_server_type]">
      <option value="imap" {selected_mail_server_type_imap}>IMAP</option>
      <option value="pop3" {selected_mail_server_type_pop3}>POP-3</option>
      <option value="imaps" {selected_mail_server_type_imaps}>IMAPS</option>
      <option value="pop3s" {selected_mail_server_type_pop3s}>POP-3S</option>
     </select>
    </td>
   </tr>
   <tr class="row_on"">
    <td>{lang_Mail_server_login_type}:</td>
    <td>
     <select name="newsettings[mail_login_type]">
      <option value="standard" {selected_mail_login_type_standard}>{lang_standard (login-name_identical_to_eGroupWare_user-name)}</option>
      <option value="vmailmgr" {selected_mail_login_type_vmailmgr}>{lang_Virtual_mail_manager_(login-name_includes_domain)}</option>
     </select>
    </td>
   </tr>
   <tr class="row_off"">
    <td>{lang_Mail_domain_(for_Virtual_mail_manager)}:</td>
    <td><input name="newsettings[mail_suffix]" value="{value_mail_suffix}"></td>
   </tr>
   <tr class="row_on">
    <td>{lang_SMTP_server_hostname_or_IP_address}:</td>
    <td><input name="newsettings[smtp_server]" value="{value_smtp_server}"></td>
   </tr>
   <tr class="row_off">
    <td>{lang_SMTP_server_port}:</td>
    <td><input name="newsettings[smtp_port]" value="{value_smtp_port}"></td>
   </tr>
   <tr class="row_on">
    <td>{lang_User_for_SMTP-authentication_(leave_it_empty_if_no_auth_required)}:</td>
    <td><input name="newsettings[smtp_auth_user]" value="{value_smtp_auth_user}"></td>
   </tr>
   <tr class="row_off">
    <td>{lang_Password_for_SMTP-authentication}:</td>
    <td><input name="newsettings[smtp_auth_passwd]" value="{value_smtp_auth_passwd}"></td>
   </tr>
   <tr class="row_on">
    <td colspan="2">&nbsp;</td>
   </tr>

<!-- from admin -->

   <tr class="th">
    <td colspan="2"><b>{lang_Authentication_/_Accounts}</b></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Select_which_type_of_authentication_you_are_using}:</td>
    <td>
     <select name="newsettings[auth_type]">
      <option value="sql"{selected_auth_type_sql}>SQL</option>
      <option value="sqlssl"{selected_auth_type_sqlssl}>SQL / SSL</option>
      <option value="ldap"{selected_auth_type_ldap}>LDAP</option>
      <option value="ads"{selected_auth_type_ads}>ADS</option>
      <option value="mail"{selected_auth_type_mail}>Mail</option>
      <option value="http"{selected_auth_type_http}>HTTP</option>
      <option value="nis"{selected_auth_type_nis}>NIS</option>
      <option value="pam"{selected_auth_type_pam}>PAM</option>
     </select>
    </td>
   </tr>


   <tr class="row_off">
    <td>{lang_Select_where_you_want_to_store/retrieve_user_accounts}:</td>
    <td>
     <select name="newsettings[account_repository]">
      <option value="sql"{selected_account_repository_sql}>SQL</option>
      <option value="ldap"{selected_account_repository_ldap}>LDAP</option>
      <!--<option value="contacts"{selected_account_repository_contacts}>Contacts - EXPERIMENTAL</option>-->
     </select>
    </td>
   </tr>

   <tr class="row_on">
    <td>{lang_sql_encryption_type}:</td>
    <td>
     <select name="newsettings[sql_encryption_type]">{hook_sql_passwdhashes}</select>
    </td>
   </tr>

   <tr class="row_off">
     <td>{lang_Activate_save_password_check}:</td>
     <td>
      <select name="newsettings[check_save_passwd]">
       <option value="">{lang_No}</option>
       <option value="True" {selected_check_save_passwd_True}>{lang_Yes}</option>
      </select>
     </td>
    </tr>
    
   <tr class="row_off">
    <td>{lang_Allow_authentication_via_cookie}:</td>
    <td>
      <select name="newsettings[allow_cookie_auth]">
         <option value="">{lang_No}</option>
         <option value="True" {selected_allow_cookie_auth_True}>{lang_Yes}</option>
       </select>
    </td>
   </tr>
   
   <tr class="row_on">
    <td>{lang_Allow_password_migration}:</td>
    <td>
      <select name="newsettings[pwd_migration_allowed]">
         <option value="">{lang_No}</option>
         <option value="True" {selected_pwd_migration_allowed_True}>{lang_Yes}</option>
       </select>
    </td>
   </tr>

   <tr class="row_on">
    <td>{lang_Allowed_migration_types_(comma-separated)}:</td>
    <td>
      <input name="newsettings[pwd_migration_types]" value="{value_pwd_migration_types}" size="20" />
    </td>
   </tr>

   <tr class="row_off">
    <td>{lang_Minimum_account_id_(e.g._500_or_100,_etc.)}:</td>
    <td><input name="newsettings[account_min_id]" value="{value_account_min_id}" /></td>
   </tr>
   <tr class="row_on">
    <td>{lang_Maximum_account_id_(e.g._65535_or_1000000)}:</td>
    <td><input name="newsettings[account_max_id]" value="{value_account_max_id}" /></td>
   </tr>
   <tr class="row_off">
    <td>{lang_User_account_prefix}:</td>
    <td><input name="newsettings[account_prefix]" value="{value_account_prefix}" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Usernames_are_casesensitive}:</td>
    <td>
      <select name="newsettings[case_sensitive_username]">
       <option value="">{lang_No}</option>
       <option value="True"{selected_case_sensitive_username_True}>{lang_Yes}</option>
      </select>
    </td>
   </tr>

   <tr class="row_off">
    <td>{lang_Auto_create_account_records_for_authenticated_users}:</td>
    <td>
      <select name="newsettings[auto_create_acct]">
       <option value="">{lang_No}</option>
       <option value="True"{selected_auto_create_acct_True}>{lang_Yes}</option>
       <option value="lowercase"{selected_auto_create_acct_lowercase}>{lang_Yes,_with lowercase_usernames}</option>
      </select>
    </td>
   </tr>

   <tr class="row_on">
    <td>{lang_Auto-created_user_accounts_expire}:</td>
    <td>
     <select name="newsettings[auto_create_expire]">
      <option value="604800"{selected_auto_create_expire_604800}>{lang_one_week}</option>
      <option value="1209600"{selected_auto_create_expire_1209600}>{lang_two_weeks}</option>
      <option value="2592000"{selected_auto_create_expire_2592000}>{lang_one_month}</option>
      <option value="never"{selected_auto_create_expire_never}>{lang_Never}</option>
     </select>
    </td>
   </tr>

   <tr class="row_off">
    <td>{lang_Add_auto-created_users_to_this_group_('Default'_will_be_attempted_if_this_is_empty.)}:</td>
    <td><input name="newsettings[default_group_lid]" value="{value_default_group_lid}" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_If_no_ACL_records_for_user_or_any_group_the_user_is_a_member_of}:</td>
    <td>
     <select name="newsettings[acl_default]">
      <option value="deny"{selected_acl_default_deny}>{lang_Deny_Access}</option>
      <option value="grant"{selected_acl_default_grant}>{lang_Grant_Access}</option>
     </select>
    </td>
   </tr>

   <tr class="row_off">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr class="th">
    <td colspan="2"><b>{lang_If_using_LDAP}:</b></td>
   </tr>
   <tr class="row_on">
    <td colspan="2"><b>{lang_You_will_need_to_load_the_proper_schema_into_your_ldap_server_-_see_phpgwapi/doc/ldap/README} !!!</b></td>
   </tr>

   <tr class="row_off">
     <td>{lang_Do_you_want_to_manage_homedirectory_and_loginshell_attributes?}:</td>
     <td>
      <select name="newsettings[ldap_extra_attributes]">
       <option value="">{lang_No}</option>
       <option value="True"{selected_ldap_extra_attributes_True}>{lang_Yes}</option>
      </select>
     </td>
    </tr>

   <tr class="row_on">
    <td>{lang_LDAP_Default_homedirectory_prefix_(e.g._/home_for_/home/username)}:</td>
    <td><input name="newsettings[ldap_account_home]" value="{value_ldap_account_home}" /></td>
   </tr>

   <tr class="row_off">
    <td>{lang_LDAP_Default_shell_(e.g._/bin/bash)}:</td>
    <td><input name="newsettings[ldap_account_shell]" value="{value_ldap_account_shell}" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_LDAP_host}:</td>
    <td><input name="newsettings[ldap_host]" value="{value_ldap_host}" /></td>
   </tr>

   <tr class="row_off">
    <td>{lang_LDAP_accounts_context}:</td>
    <td><input name="newsettings[ldap_context]" value="{value_ldap_context}" size="40" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_LDAP_search_filter_for_accounts,_default:_"(uid=%user)",_%domain=eGW-domain}:</td>
    <td><input name="newsettings[ldap_search_filter]" value="{value_ldap_search_filter}" size="40" /></td>
   </tr>

   <tr class="row_off">
    <td>{lang_LDAP_groups_context}:</td>
    <td><input name="newsettings[ldap_group_context]" value="{value_ldap_group_context}" size="40" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_LDAP_rootdn} {lang_(searching_accounts_and_changing_passwords)}:</td>
    <td><input name="newsettings[ldap_root_dn]" value="{value_ldap_root_dn}" size="40" /></td>
   </tr>

   <tr class="row_off">
    <td>{lang_LDAP_root_password}:</td>
    <td><input name="newsettings[ldap_root_pw]" type="password" value="{value_ldap_root_pw}" /></td>
   </tr>

   <tr class="row_on">
    <td>{lang_LDAP_encryption_type}:</td>
    <td>
     <select name="newsettings[ldap_encryption_type]">
{hook_passwdhashes}
     </select>
    </td>
   </tr>

   <tr class="row_off">
     <td>{lang_Enable_LDAP_Version_3}:</td>
     <td>
      <select name="newsettings[ldap_version3]">
       <option value="">{lang_No}</option>
       <option value="True" {selected_ldap_version3_True}>{lang_Yes}</option>
      </select>
     </td>
    </tr>

   <tr class="row_on">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr class="th">
    <td colspan="2"><b>{lang_If_using_ADS_(Active_Directory)_authentication}:</b></td>
   </tr>
   <tr class="row_off">
     <td>{lang_Host/IP_Domain_controler}:</td>
     <td><input name="newsettings[ads_host]" value="{value_ads_host}" size="40" /></td>
   </tr>
   <tr class="row_on">
     <td>{lang_Domain_name}:</td>
     <td><input name="newsettings[ads_domain]" value="{value_ads_domain}" size="40" /></td>
   </tr>

   <tr class="row_off">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr class="th">
    <td colspan="2"><b>{lang_Mcrypt_settings_(requires_mcrypt_PHP_extension)}</b></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Enter_some_random_text_for_app_session_encryption}:</td>
    <td><input name="newsettings[encryptkey]" value="{value_encryptkey}" size="40" /></td>
   </tr>

   <tr class="row_off">
    <td>{lang_Mcrypt_algorithm_(default_TRIPLEDES)}:</td>
    <td>
     <select name="newsettings[mcrypt_algo]">
{hook_encryptalgo}
     </select>
    </td>
   </tr>

   <tr class="row_on">
    <td>{lang_Mcrypt_mode_(default_CBC)}:</td>
    <td>
     <select name="newsettings[mcrypt_mode]">
{hook_encryptmode}
     </select>
    </td>
   </tr>

   <tr class="row_off">
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr class="th">
    <td colspan="2"><b>{lang_Additional_settings}</b></td>
   </tr>
   <tr class="row_on">
    <td>
	{lang_Select_where_you_want_to_store/retrieve_filesystem_information}:
	<br />
	({lang_file_type,_size,_version,_etc.})
    </td>
    <td>
     <select name="newsettings[file_repository]">
      <option value="sql"{selected_file_repository_sql}>SQL ({lang_default})</option>
      <option value="dav"{selected_file_repository_dav}>WebDAV</option>
      <option value="sql2"{selected_file_repository_sql2}>SQL version 2 (EXPERIMENTAL, required and works only with FilesCenter)</option>
     </select>
    </td>
   </tr>
   <tr class="row_off">
    <td>
	{lang_Select_where_you_want_to_store/retrieve_file_contents}:
	<br />
	({lang_Recommended:_Filesystem})
    </td>
    <td>
     <select name="newsettings[file_store_contents]">
      <option value="filesystem"{selected_file_store_contents_filesystem}>{lang_Filesystem}</option>
      <option value="sql"{selected_file_store_contents_sql}>SQL</option>
     </select>
    </td>
   </tr>
   <tr class="row_on">
    <td colspan="2">&nbsp;</td>
   </tr>

<!-- end from admin -->

<!-- END body -->

<!-- BEGIN footer -->
  <tr class="th">
    <td colspan="2">
	&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" name="submit" value="Submit" />
      <input type="submit" name="cancel" value="Cancel" />
    </td>
  </tr>
</table>
</form>
<!-- END footer -->


