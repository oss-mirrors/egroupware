<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                        
<hr noshade width="98%" align="center" size="1">                                                                                                                                                     
<center>

 {total_matchs}
 {next_matchs}
	
  <table width=100% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="15%" bgcolor="{th_bg}" align=center>{sort_project}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_activity}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_remark}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
      <td width="5%" bgcolor="{th_bg}" align=center>{sort_minutes}</td>
      <td width="5%" align=center>{h_lang_view}</td>
      <td width="5%" align=center>{h_lang_edit}</td>
    </tr>
  </form>
  
<!-- BEGIN hours_list -->

      <tr bgcolor="{tr_color}">
        <td>{project}</td>
        <td>{activity}</td>
        <td>{remark}</td>
        <td align=center>{status}</td>
        <td align=center>{end_date}</td>
        <td align=right>{minutes}</td>
        <td align=center><a href="{view}">{lang_view}</a></td>
        <td align=center><a href="{edit}">{lang_edit}</a></td>
      </tr>

<!-- END hours_list -->

  </table>
</center>
