<center>
{lang_projecthours_action}

 {total_matchs}
 {next_matchs}
	
  <table width=100% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_activity}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_remark}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_date}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_minutes}</td>
      <td width="9%" align=center>{h_lang_edithour}</td>
      <td width="9%" align=center>{h_lang_deletehour}</td>
    </tr>
  </form>
  
<!-- BEGIN projecthours_list -->
      <tr bgcolor="{tr_color}">
        <td>{activity}</td>
        <td>{remark}</td>
        <td align=center>{status}</td>
        <td align=center>{date}</td>
        <td align=center>{end_date}</td>
        <td align=right>{minutes}</td>
        <td align=center>{edithour}</td>
        <td align=center>{deletehour}</td>
      </tr>
<!-- END projecthours_list -->

  </table>
</center>
