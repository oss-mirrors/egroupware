<!-- BEGIN header -->
<form name=frm method="POST" action="{action_url}">
<table border="0" align="center">
   <tr bgcolor="{th_bg}">
    <td colspan="2" align="center"><font size="+1" color="{th_text}"><b>JiNN - {title}</b></font></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
   <tr>
   <td colspan="2">&nbsp;</td>
   </tr>

	<tr bgcolor="{row_on}">
    <td colspan="2"><b>{lang_General_Settings}</b></td>
   </tr>

   <tr bgcolor="{row_off}">
   <td colspan="2"><b>{lang_Image_settings}</b></td>
   </tr>
   <tr >
   <td>{lang_Select_which_graphic_library_JiNN_must_use}</td>
   <td>
   <select name="newsettings[use_magick]">
   <option value="GDLIB" {selected_use_magick_GDLIB}>GDLib</option>
   <option value="MAGICK" {selected_use_magick_MAGICK}>ImageMagick</option>
   </select>
   </td>
   </tr>

	<tr bgcolor="{row_off}">
   <td>
   {lang_Notice_that_JiNN_needs_ImageMagick_5.4.9_or_a_later_version}
   <br/>{lang_Path_to_convert_from_ImageMagick_(_e.g._/usr/X11R6/bin_)}:</td>
   <td><input name="newsettings[imagemagickdir]" size="30" value="{value_imagemagickdir}"></td>
   </tr>
   
	<tr bgcolor="{row_off}">
	<td>
	{lang_Walk_events_plugin_max_numbers_to_loop}:</td>
	<td><input name="newsettings[loop_numbers]" size="30" value="{value_loop_numbers}"></td>
	</tr>
	
	 <tr >
   <td>
   {lang_Use_reports}
   <br/>{lang_This_allows_the_users_to_use_the_report_functionalitie}:</td>
   <td>
   <select name="newsettings[report_on]">
   		<option value="On" {selected_report_on_On}>On</option>
   		<option value="Off" {selected_report_on_Off}>Off</option>
   </select>
   </td>
   </tr>
   <tr>
   <td colspan="2">&nbsp;</td>
   </tr>

<!-- END body -->
<!-- BEGIN footer -->
<tr bgcolor="{th_bg} ">
<td colspan="2" align="center">
<input class="egwbutton"  type="submit" name="submit" value="{lang_submit}">
<input class="egwbutton"  type="submit" name="cancel" value="{lang_cancel}">
</td>
</tr>
</table>
</form>
<!-- END footer -->
