<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
{hidden_vars}
<table border="0" cellspacing="2" cellpadding="2">
 <tr>
  <td colspan="5" align="left">
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
  <td colspan="5" align="right">
  <form method="post" action="{searchurl}">
  <input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}">
  </form></td>
 </tr>
    <tr bgcolor="{th_bg}">
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_num}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_customer}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_title}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_date}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{h_lang_delivery}</td>
    </tr>
  
<!-- BEGIN projects_list -->

      <tr bgcolor="{tr_color}">
        <td>{num}</td>
        <td>{customer}</td>
        <td>{title}</td>
        <td align=center>{date}</td>
        <td align=center><a href="{delivery}">{lang_delivery}</a></td>
      </tr>

<!-- END projects_list -->

  </table>
</center>
