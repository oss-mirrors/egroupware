<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
{hidden_vars}
<table border="0" cellspacing="2" cellpadding="2">
 <tr>
  <td colspan="4" align="left">
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
  <td colspan="4" align=right>
  <form method="post" action="{searchurl}">
  <input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}">
  </form></td>
 </tr>
    <tr bgcolor="{th_bg}">
      <td width="20%" align=center bgcolor="{th_bg}" align=center>{sort_lid}</td>
      <td width="20%" align=center bgcolor="{th_bg}" align=center>{sort_firstname}</td>
      <td width="20%" align=center bgcolor="{th_bg}">{sort_lastname}</td>
      <td align=center width="8%" bgcolor="{th_bg}">{lang_stat}</td>
    </tr>
  
<!-- BEGIN user_list -->

      <tr bgcolor="{tr_color}">
        <td>{lid}</td>
        <td>{firstname}</td>
	<td>{lastname}</td>
        <td align=center><a href="{stat}">{lang_stat}</a></td>
      </tr>

<!-- END user_list -->

  </table>
</center>