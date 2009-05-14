<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table border="0" align="center">
   <tr class="th">
    <td colspan="2"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
   </tr>
<!-- END header -->

<!-- BEGIN body -->
   <tr class="row_on">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr class="row_off">
    <td colspan="2"><b>{lang_Calendar} {lang_site_configuration}</b></td>
   </tr>
   <tr class="row_on">
    <td>{lang_Do_you_wish_to_autoload_calendar_holidays_files_dynamically?}</td>
    <td>
     <select name="newsettings[auto_load_holidays]">
      <option value=""{selected_auto_load_holidays_False}>{lang_No}</option>
      <option value="True"{selected_auto_load_holidays_True}>{lang_Yes}</option>
     </select>
    </td>
   </tr>
   <tr class="row_off">
    <td>{lang_Location_to_autoload_from}:</td>
    <td>
     <select name="newsettings[holidays_url_path]">
      <option value="localhost"{selected_holidays_url_path_localhost}>localhost</option>
      <option value="http://www.egroupware.org/cal"{selected_holidays_url_path_http://www.egroupware.org/cal}>www.egroupware.org</option>
     </select>
    </td>
   </tr>
   <!-- lock setting -->
   <tr class="row_on">
   <td>{lang_setting_lock_time_calender}:</td>
    <td><input name="newsettings[Lock_Time_Calender]" value="{value_Lock_Time_Calender}" size="40"></td>
   </tr>
   <tr class="row_off">
    <td>{lang_Deny_Ressources_reservation_for_private_events}:</td>
    <td>
     <select name="newsettings[no_ressources_private]">
      <option value="">{lang_No}</option>
      <option value="yes"{selected_no_ressources_private_yes}>{lang_Yes}</option>
     </select>
    </td>
   </tr>
   <tr class="row_on">
    <td colspan="2"><b>{lang_Birthdays}</b></td>
   </tr>
   <tr class="row_off">
    <td>{lang_Show_birthdays_from_addressbook}:</td>
    <td>
     <select name="newsettings[hide_birthdays]">
      <option value="">{lang_Yes}</option>
      <option value="dateonly"{selected_hide_birthdays_dateonly}>{lang_Show_only_the_date,_not_the_year}</option>
      <option value="yes"{selected_hide_birthdays_yes}>{lang_No}</option>
     </select>
    </td>
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
      <input type="submit" name="submit" value="{lang_submit}">
      <input type="submit" name="cancel" value="{lang_cancel}">
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
