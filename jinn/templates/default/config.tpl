<!-- BEGIN header -->

<SCRIPT language=JavaScript src="jinn/javascript/display_func.js" type=text/javascript></script>
<form name=frm method="POST" action="{action_url}">
<table border="0" align="left">
   <tr bgcolor="{th_bg}">
    <td colspan="2"><font color="{th_text}">&nbsp;<b>jinn - {title}</b></font></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
   <tr bgcolor="{row_on}">
   <td colspan="2">&nbsp;</td>
  </tr>

  <tr bgcolor="{row_off}">
   <td colspan="2">&nbsp;<b>{lang_General_settings}</b></td>
  </tr>

  <tr bgcolor="{row_on}">
   <td>{lang_Filebrowser_starting_directory_(_default=[/]_)}:</td>
   <td><input name="newsettings[filebrowser_start_dir]" size="60" value="{value_filebrowser_start_dir}">
   <input type=button onClick='PcjsOpenExplorer("jinn/inc/pcsexplorer.php", "forms.frm.newsettings[value_filebrowser_start_dir].value", "type=file", "calling_dir=", "start_dir=")' value="{lang_select_executable}">

   
   </td>
  </tr>
  <tr bgcolor="{row_on}">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr bgcolor="{row_off}">
    <td colspan="2">&nbsp;<b>{lang_Image_settings}</b></td>
   </tr>

   <tr bgcolor="{row_on}">
    <td>{lang_convert_exec_(_e.g._/usr/X11R6/bin/convert_)}:</td>
    <td><input name="newsettings[convert_exec]" size="30" value="{value_convert_exec}">
	<input type=button onClick='PcjsOpenExplorer("jinn/inc/pcsexplorer.php", "forms.frm.newsettings[convert_exec].value", "type=file", "calling_dir=", "start_dir=")' value="{lang_select_executable}">

	
	</td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_default_image_width}:</td>
    <td><input name="newsettings[default_image_width]" size="4" value="{value_default_image_width}"></td>
   </tr>

   <tr bgcolor="{row_on}">
    <td>{lang_default_thumb_width}:</td>
    <td><input name="newsettings[default_thumb_width]" size="4" value="{value_default_thumb_width}"></td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_default_image_type (jpg, gif, png)}:</td>
    <td><input name="newsettings[default_image_type]" size="3" value="{value_default_image_type}"></td>
   </tr>

   <tr bgcolor="{row_on}">
    <td colspan="2">&nbsp;<b>{lang_Browse_through_object_settings}</b></td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Show_upload_path}:</td>
    <td><input name="newsettings[show_upload_path]" size="3" value="{value_show_upload_path}"></td>
   </tr>



<!-- END body -->
<!-- BEGIN footer -->
  <tr bgcolor="{th_bg}">
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
