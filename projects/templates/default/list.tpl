<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                  
<hr noshade width="98%" align="center" size="1">
<center>
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
  <td colspan="8" align=right>
  <form method="post" action="{searchurl}">
  <input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}">
  </form></td>
 </tr>
    <tr bgcolor="{th_bg}">
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_number}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_customer}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_title}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_coordinator}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{lang_view}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{lang_edit}</td>
    </tr>
  
<!-- BEGIN projects_list -->

      <tr bgcolor="{tr_color}">
        <td>{number}</td>
	<td>{customer}</td>
        <td>{title}</td>
        <td>{coordinator}</td>
        <td align=center>{status}</td>
        <td align=center>{end_date}</td>
        <td align=center><a href="{view}">{lang_view_entry}</a></td>
        <td align=center><a href="{edit}">{lang_edit_entry}</a></td>
      </tr>

<!-- END projects_list -->

  </table>
  <table cellpadding=3 cellspacing=1>
  <form method="POST" action="{addurl}">
  {common_hidden_vars}
    <tr> 
      <td><input type="submit" name="Add" value="{lang_add}"></form></td>
  <form method="POST" action="{activitiesurl}">
      <td><input type="submit" name="Add" value="{lang_activities}"></form></td>
    </tr>
  </table>
</center>
