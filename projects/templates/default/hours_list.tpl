<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                  
<hr noshade width="98%" align="center" size="1">
<center>
 {total_matchs}
 {next_matchs}
	
  <table width=100% cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_num}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_customer}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_title}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_coordinator}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{h_lang_addhour}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{h_lang_viewhour}</td>
    </tr>
  </form>
  
<!-- BEGIN projects_list -->
      <tr bgcolor="{tr_color}">
        <td>{num}</td>
        <td>{customer}</td>
	<td>{title}</td>
        <td>{coordinator}</td>
        <td align=center>{status}</td>
        <td align=center>{end_date}</td>
        <td align=center>{addhour}</td>
        <td align=center>{viewhour}</td>
      </tr>
<!-- END projects_list -->

  </table>
  <table cellpadding=3 cellspacing=1>
  <tr>
     <td><a href={all_open_hours}>{lang_all_open_hours}</a></td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td><a href={all_done_hours}>{lang_all_done_hours}</a></td>
  </tr>
  </table>
</center>
