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
    <td colspan="2">&nbsp;<b>{lang_Image_settings}</b></td>
   </tr>
   <tr bgcolor="{row_on}">
    <td>{lang_ImageMagick_directory_(_e.g._/usr/X11R6/bin_)}:</td>
    <td><input name="newsettings[imagemagickdir]" size="30" value="{value_imagemagickdir}">
	<input type=button onClick='PcjsOpenExplorer("jinn/inc/pcsexplorer.php", "forms.frm.newsettings[imagemagickdir].value", "type=dir", "calling_dir=", "start_dir=")' value="{lang_select_directory}">
	</td>
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
