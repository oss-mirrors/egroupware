<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                  
<hr noshade width="98%" align="center" size="1">
<center>
{hidden_vars}
<table border="0" width="100%">	
    <tr>
    <td width="33%" align="left">
    <form action="{cat_url}" name="form" method="POST">
    {lang_category}&nbsp;&nbsp;<select name="cat_id" onChange="this.form.submit();"><option value="">{lang_all}</option>{category_list}</select>
    <noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript></form></td>
    <td width="33%" align="center">{lang_showing}</td>
    <td width="33%" align="right">
    <form method="POST" action="{search_url}">
    <input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}">
    </form></td>
    </tr>
    <tr>
    <td colspan="10">
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
<table border="0" width="100%" cellpadding="2" cellspacing="2">
    <tr bgcolor="{th_bg}">
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_number}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_customer}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_title}</td>
      <td width="15%" bgcolor="{th_bg}" align=center>{sort_coordinator}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{lang_jobs}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{lang_delivery}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{lang_invoice}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{lang_stats}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{lang_edit}</td>
    </tr>

<!-- BEGIN projects_list -->

      <tr bgcolor="{tr_color}">
        <td>{number}</td>
	<td>{customer}</td>
        <td>{title}</td>
        <td>{coordinator}</td>
        <td align=center>{end_date}</td>
        <td align=center><a href="{jobs}">{lang_jobs}</a></td>
        <td align=center><a href="{delivery}">{lang_delivery}</a></td>
        <td align=center><a href="{invoice}">{lang_invoice}</a></td>
        <td align=center><a href="{stats}">{lang_stats}</a></td>
        <td align=center><a href="{edit}">{lang_edit_entry}</a></td>
      </tr>

<!-- END projects_list -->

  </table>
</center>