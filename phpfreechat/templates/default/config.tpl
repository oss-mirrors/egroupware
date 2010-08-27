<!-- BEGIN header -->
<form method="POST" action="{action_url}">
{hidden_vars}
<table border="0" align="center">
   <tr class="th">
	   <td colspan="2"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;<i><font color="red">{error}</i></font></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
   <tr class="th">
    <td colspan="2">&nbsp;<b>{lang_phpfreechat_configuration}</b></td>
   </tr>
   <tr class="row_on">
    <td>&nbsp;{lang_frozen_nick}:</td>
    <td>
	 <select name="newsettings[frozen_nick]">
	  <option value="True"{selected_frozen_nick_True}>{lang_Yes}</option>
	  <option value="False"{selected_frozen_nick_False}>{lang_No}</option>
	 </select>
	</td>
   </tr>
   <tr class="row_off">
	<td colspan="2">&nbsp;{lang_are_users_allowed_to_change_their_nicknames_in_phpfreechat_AND_are_allowed_to_connect_multiple_times}</td>
   </tr>
   <tr class="row_on">
    <td>&nbsp;{lang_nick_length}:</td>
    <td><input name="newsettings[max_nick_len]" value="{value_max_nick_len}" size="3" /></td>
   </tr>
   <tr class="row_off">
	<td colspan="2">&nbsp;{lang_maximum_nickname_length_(if_not_set_the_length_of_64_is_used._as_it_is_the_maximum_lenght_of_usernames_within_egroupware)}</td>
   </tr>
<!-- END body -->
<!-- BEGIN footer -->
  <tr valign="bottom" style="height: 30px;">
    <td colspan="2" align="center">
      <input type="submit" name="submit" value="{lang_submit}">
      <input type="submit" name="cancel" value="{lang_cancel}">
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
