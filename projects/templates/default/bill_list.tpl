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
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_num}</td>
      <td width="20%" align=center>{sort_customer}</td>
      <td width="20%" align=center>{sort_title}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_coordinator}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
      <td width="8%" align=center>{h_lang_invoice}</td>
      <td width="8%" align=center>{h_lang_invoicelist}</td>
    </tr>
  </form>
  
<!-- BEGIN projects_list -->
      <tr bgcolor="{tr_color}">
        <td>{number}</td>
        <td>{customer}</td>
	<td>{title}</td>
        <td>{coordinator}</td>
        <td align=center>{status}</td>
        <td align=center>{end_date}</td>
        <td align=center><a href="{invoice}">{lang_invoice}</a></td>
        <td align=center><a href="{invoicelist}">{lang_invoicelist}</a></td>
      </tr>
<!-- END projects_list -->

  </table><br><br>

<!-- link fuer alle invoices -->
      <table cellpadding=3 cellspacing=1>     
     <tr>
    <td><a href="{all_invoicelist}">{lang_all_invoicelist}</a></td>
   </tr>
  </table>

</center>
