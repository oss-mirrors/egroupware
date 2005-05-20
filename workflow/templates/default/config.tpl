<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table border="0" align="center" cellspacing="1" cellpading="1" width="90%">
   <tr class="th">
	   <td colspan="3"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
   </tr>
   <tr class="row_on" bgcolor="{th_err}">
    <td colspan="3">&nbsp;<b>{error}</b></font></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
	<tr class="th">
		<td colspan="3">&nbsp;<b>{lang_Workflow_configuration}</b></font></td>
	</tr>
	<tr class="row_on">
		<td colspan="3">&nbsp;<b>{error}</b></font></td>
	</tr>
	<tr class="th">
		<td colspan="2">&nbsp;<b>{lang_Default_settings_for_processes}</b></font></td>
		<td class="row_off">&nbsp;</td>
	</tr>
	<tr class="th">
		<td>&nbsp;<b>{lang_Graphic_options}</b></font></td>
		<td colspan="2" class="row_on">&nbsp;</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_draw_roles_on_the_graph_beside_activity_name_like_[that]}
		</td>
		<td width="20%" class="td_right">
			{lang_draw_roles}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[draw_roles]">
                                <option value="" {selected_draw_roles_False}>{lang_No}</option>
                                <option value="True" {selected_draw_roles_True}>{lang_Yes}</option>
                        </select>
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_size_of_the_font_used_on_the_graph_12_should_be_a_good_default_value}
		</td>
		<td width="20%" class="td_right">
			{lang_font_size}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="3" name="newsettings[font_size]" value="{value_font_size}">
		</td>
	</tr>
	<tr class="th">
		<td>&nbsp;<b>{lang_Workflow_engine_options}</b></font></td>
		<td colspan="2" class="row_off">&nbsp;</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_this_as_actually_no_effect}
		</td>
		<td width="20%" class="td_right">
			{lang_ownership_give_all_rights}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[ownership_give_rights]">
                               	<option value="" {selected_ownership_give_rights_False}>{lang_No}</option>
                                <option value="True" {selected_ownership_give_rights_True}>{lang_Yes}</option>
       	                </select>
		</td>
	</tr>
<!-- END body -->
<!-- BEGIN footer -->
  <tr class="row_off">
    <td colspan="3">
&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan="3" align="center">
      <input type="submit" name="submit" value="{lang_submit}">
      <input type="submit" name="cancel" value="{lang_cancel}">
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
