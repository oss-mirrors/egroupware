<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                        
<hr noshade width="98%" align="center" size="1">                                                                                                                                                     
<center>
 {hidden_vars}
 {error}
<table border="0" cellspacing="2" cellpadding="2">
 <tr>
  <td colspan="8" align="left">
   <table border="0" width="100%">
    <tr>
    {left}
    <td align="center">{lang_showing}</td>
    {right}
    </tr>
   </table>
   </td>
  </tr>
 <tr>
  <td>&nbsp;</td>
  <td colspan="8" align="right">
  <form method="post" action="{searchurl}">
  <input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}">
  </form></td>
 </tr>
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
