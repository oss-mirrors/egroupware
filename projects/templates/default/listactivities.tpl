<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                      
<hr noshade width="98%" align="center" size="1">
<center> 
{total_matchs}
 {next_matchs}
  {error}	
  <table width=85% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_num}</td>
      <td width="30%" bgcolor="{th_bg}" align=center>{sort_descr}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{currency}&nbsp;{sort_billperae}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_minperae}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{h_lang_edit}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{h_lang_delete}</td>
    </tr>
  </form>
  
<!-- BEGIN activities_list -->
      <tr bgcolor="{tr_color}">
        <td>{num}</td>
        <td>{descr}</td>
        <td align=right>{billperae}</td>
        <td align=right>{minperae}</td>
	<td align=center>{edit}{subadd}</td>
        <td align=center>{delete}</td>
      </tr>
<!-- END activities_list -->

  </table>
  <table border=0 cellpadding=3 cellspacing=1>
  <tr>
  <form method="POST" action="{actionurl}">
  {common_hidden_vars} 
      <td><input type="submit" name="Add" value="{lang_add}"></form></td>
  <td><form method="POST" action="{projectsurl}"> 
      <input type="submit" name="Add" value="{lang_projects}"></form></td>
    </tr>
  </table>
</center>
