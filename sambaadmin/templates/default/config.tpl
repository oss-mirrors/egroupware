<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table border="0" align="center">
   <tr class="th">
    <td colspan="2">&nbsp;<b>{title}</b></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
   <tr class="row_off">
    <td>{lang_Samba_SID}:</td>
    <td><input size="60" name="newsettings[sambasid]" value="{value_sambasid}"></td>
   </tr>

   <tr class="row_on">
    <td>{lang_computer_ou}:</td>
    <td><input size="60" name="newsettings[samba_computerou]" value="{value_samba_computerou}"></td>
   </tr>

   <tr class="row_off">
    <td>{lang_computer_group}:</td>
    <td><input size="60" name="newsettings[samba_computergroup]" value="{value_samba_computergroup}"></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Generate_old_Lanmanager_password_hash?}</td>
    <td>
     <select name="newsettings[samba_lmpassword]">
      <option value="">{lang_No} - {lang_more_secure}</option>
      <option value="yes"{selected_samba_lmpassword_yes}>{lang_Yes}</option>
     </select>
    </td>
   </tr>

   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr class="th">
    <td colspan="2">&nbsp;<b>{lang_new_account_defaults}</b></td>
   </tr>

   <tr class="row_on">
    <td>{lang_smb_homepath}:</td>
    <td><input size="60" name="newsettings[samba_homepath]" value="{value_samba_homepath}"></td>
   </tr>

   <tr class="row_off">
    <td>{lang_homedrive}:</td>
    <td><input size="60" name="newsettings[samba_homedrive]" value="{value_samba_homedrive}"></td>
   </tr>

   <tr class="row_on">
    <td>{lang_logonscript}:</td>
    <td><input size="60" name="newsettings[samba_logonscript]" value="{value_samba_logonscript}"></td>
   </tr>

   <tr class="row_off">
    <td>{lang_profilepath}:</td>
    <td><input size="60" name="newsettings[samba_profilepath]" value="{value_samba_profilepath}"></td>
   </tr>
<!-- END body -->
<!-- BEGIN footer -->
  <tr class="th">
    <td colspan="2">
&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" name="submit" value="{lang_submit}" class="et2_button et2_button_text">
      <input type="submit" name="cancel" value="{lang_cancel}" class="et2_button et2_button_text">
		  <br>
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
