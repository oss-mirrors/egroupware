<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                        
<hr noshade width="98%" align="center" size="1">                                                                                                                                                     
<center>
<table border="0" width="100%">
    <tr>
    <td width="33%" align="left">
    <form action="{project_action}" method="POST">
    <select name="filter"><option value="">{lang_select_project}</option>{project_list}</select>
    &nbsp; <input type="submit" name="submit" value="{lang_submit}"></form></td>
    <td width="33%" align="center">{lang_showing}</td>
    <td width="33%" align="right">
    <form method="POST" action="{search_action}">
    <input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}">
    </form></td>
    </tr>
    <tr>
    <td colspan="7">
    <table border="0" width="100%">
    <tr>
    {left}
    <td>&nbsp;</td>
    {right}
    </tr>
    </table>
    </td>
    </tr>
</table>

{error}<br><br>
<table border="0" width="100%" cellpadding="2" cellspacing="2">
    <tr bgcolor="{th_bg}">
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_activity}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_hours_descr}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_start_date}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
      <td width="5%" bgcolor="{th_bg}" align=center>{sort_minutes}</td>
      <td width="5%" align=center>{h_lang_view}</td>
      <td width="5%" align=center>{h_lang_edit}</td>
    </tr>
  
<!-- BEGIN hours_list -->

      <tr bgcolor="{tr_color}">
        <td>{activity}</td>
        <td>{hours_descr}</td>
        <td align=center>{status}</td>
        <td align=center>{start_date}</td>
        <td align=center>{end_date}</td>
        <td align=right>{minutes}</td>
        <td align=center><a href="{view}">{lang_view}</a></td>
        <td align=center><a href="{edit}">{lang_edit}</a></td>
      </tr>

<!-- END hours_list -->

<!-- BEGINN add   -->

<tr>
  <td vailgn="bottom" height="62">
     <form method="POST" action="{add_action}">
      <input type="submit" value="{lang_add}">
     </form>
    </td>
   </tr>

<!-- END add -->

  </table>
</center>
