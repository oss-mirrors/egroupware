<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                             
<hr noshade width="98%" align="center" size="1">
<center>
{total_matchs}
 {next_matchs}
  <table width=100% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="8%" align=center bgcolor="{th_bg}" align=center>{sort_num}</td>
      <td width="20%" align=center bgcolor="{th_bg}" align=center>{sort_customer}</td>
      <td width="20%" align=center align=center>{sort_title}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_coordinator}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
      <td align=center width="5%">{h_lang_stat}</td>
    </tr>
  </form>
  
<!-- BEGIN project_list -->
      <tr bgcolor="{tr_color}">
        <td>{number}</td>
        <td>{customer}</td>
	<td>{title}</td>
        <td>{coordinator}</td>
        <td align=center>{status}</td>
        <td align=center>{end_date}</td>
        <td align=center><a href="{stat}">{lang_stat}</a></td>
      </tr>
<!-- END project_list -->
   </table>
  <table cellpadding=3 cellspacing=1>                                                                                                                                           
    <tr>                                                                                                                                                                        
     <form method="POST" action="{userlisturl}">                                                                                                                                 
      <td><input type="submit" name="submit" value="{lang_userlist}"></form></td>                                                                                                
    </tr>                                                                                                                                                                       
  </table>
</center>
