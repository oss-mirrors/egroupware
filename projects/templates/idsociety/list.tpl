<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                  
<hr noshade width="98%" align="center" size="1">
<center>
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
    <td colspan="9">
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
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_customer}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_title}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_coordinator}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
      <td width="5%" bgcolor="{th_bg}" align=center>{lang_jobs}</td>
      <td width="5%" bgcolor="{th_bg}" align=center>{lang_view}</td>
      <td width="5%" bgcolor="{th_bg}" align=center>{lang_edit}</td>
    </tr>
  
<!-- BEGIN projects_list -->

      <tr bgcolor="{tr_color}">
        <td>{number}</td>
	<td>{customer}</td>
        <td>{title}</td>
        <td>{coordinator}</td>
        <td align=center>{status}</td>
        <td align=center>{end_date}</td>
        <td align=center><a href="{jobs}">{lang_jobs}</a></td>
        <td align=center><a href="{view}">{lang_view_entry}</a></td>
        <td align=center><a href="{edit}">{lang_edit_entry}</a></td>
      </tr>

<!-- END projects_list -->

    <tr valign="bottom">
    <td height="50">
  <form method="POST" action="{add_url}">
  {hidden_vars}
    <input type="submit" name="Add" value="{lang_add}"></form></td>
    </tr>
  </table>
</center>