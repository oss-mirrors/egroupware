<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table border="0" align="center">
   <tr bgcolor="{th_bg}">
    <td colspan="2"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
   <tr bgcolor="{row_on}">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr bgcolor="{row_off}">
    <td colspan="2">&nbsp;<b>{lang_Preferences}</b></td>
   </tr>

   <tr bgcolor="{row_on}">
    <td>{lang_Enter_the_title_for_your_site}.</td>
    <td><input name="newsettings[site_title]" value="{value_site_title}"></td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Show_'powered_by'_logo_on}:</td>
    <td>
     <select name="newsettings[showpoweredbyon]">
      <option value="bottom" {selected_showpoweredbyon_bottom}>bottom</option>
      <option value="top" {selected_showpoweredbyon_top}>top</option>
     </select>
    </td>
   </tr>

   <tr bgcolor="{row_on}">
    <td>{lang_Country_Selection} ({lang_Text_Entry}/{lang_SelectBox}):</td>
    <td>
     <select name="newsettings[countrylist]">
{hook_country_set}
     </select>
    </td>
   </tr>

   <tr bgcolor="{row_on}">
    <td>{lang_Interface}/{lang_Template_Selection}:</td>
    <td>
     <select name="newsettings[template_set]">
{hook_template_set}
     </select>
    </td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Use_theme}:<br></td>
    <td>
     <select name="newsettings[force_theme]">
{hook_force_theme}
     </select>
    </td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Force_default_application}:<br></td>
    <td>
     <select name="newsettings[force_default_app]">
{hook_force_default_app}
     </select>
    </td>
   </tr>

   <tr bgcolor="{row_on}">
    <td>{lang_Use_pure_HTML_compliant_code_(not_fully_working_yet)}:</td>
    <td>
     <select name="newsettings[htmlcompliant]">
      <option value="">No</option>
      <option value="True"{selected_htmlcompliant_True}>Yes</option>
     </select>
    </td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Use_cookies_to_pass_sessionid}:</td>
    <td>
     <select name="newsettings[usecookies]">
      <option value="">No</option>
      <option value="True"{selected_usecookies_True}>Yes</option>
     </select>
    </td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_check_ip_address_of_all_sessions}:</td>
    <td>
     <select name="newsettings[sessions_checkip]">
      <option value="">No</option>
      <option value="True"{selected_sessions_checkip_True}>Yes</option>
     </select>
    </td>
   </tr>

   <tr bgcolor="{row_on}">
    <td>{lang_Would_you_like_phpGroupWare_to_check_for_a_new_version<br>when_admins_login_?}:</td>
    <td>
     <select name="newsettings[checkfornewversion]">
      <option value="">No</option>
      <option value="True"{selected_checkfornewversion_True}>Yes</option>
     </select>
    </td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Would_you_like_phpGroupWare_to_cache_the_phpgw_info_array ?}:</td>
    <td>
     <select name="newsettings[cache_phpgw_info]">
      <option value="">No</option>
      <option value="True"{selected_cache_phpgw_info_True}>Yes</option>
     </select>
    </td>
   </tr>
<!--
   <tr bgcolor="{row_off}">
     <td>{lang_Default_file_system_space_per_user}/{lang_group_?}:</td>
     <td>
      <input type="text" name="newsettings[vfs_default_account_size_number]" size="7" value="{value_vfs_default_account_size_number}">&nbsp;&nbsp;
      <select name="newsettings[vfs_default_account_size_type]">
       <option value="gb"{selected_vfs_default_account_size_type_gb}>GB</option>
       <option value="mb"{selected_vfs_default_account_size_type_mb}>MB</option>
       <option value="kb"{selected_vfs_default_account_size_type_kb}>KB</option>
       <option value="b"{selected_vfs_default_account_size_type_b}>B</option>
      </select>
     </td>
    </tr>
-->
<!-- END body -->

<!-- BEGIN footer -->
  <tr bgcolor="{th_bg}">
    <td colspan="2">
&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" name="submit" value="Submit">
      <input type="submit" name="cancel" value="Cancel">
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
