<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                  
<hr noshade width="98%" align="center" size="1">
<center>
 {total_matchs}
 {next_matchs}
	
  <table width=100% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_num}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_customer}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_title}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_coordinator}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{h_lang_edit}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{h_lang_delete}</td>
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
        <td align=center>{edit}{subadd}</td>
        <td align=center>{delete}</td>
      </tr>
<!-- END projects_list -->

  </table>
  <table cellpadding=3 cellspacing=1>
  <form method="POST" action="{actionurl}">
  {common_hidden_vars}
    <tr> 
      <td><input type="submit" name="Add" value="{lang_add}"></form></td>
  <form method="POST" action="{activitiesurl}">
      <td><input type="submit" name="Add" value="{lang_activities}"></form></td>
    </tr>
  </table>
</center>
