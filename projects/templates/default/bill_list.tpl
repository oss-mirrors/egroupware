<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                  
<hr noshade width="98%" align="center" size="1">
<center>
 {total_matchs}
 {next_matchs}
	
  <table width=100% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_num}</td>
      <td width="20%" align=center>{sort_customer}</td>
      <td width="20%" align=center>{sort_title}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_coordinator}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="8%" bgcolor="{th_bg}" align=center>{sort_end_date}</td>
<!--  <td width="8%" align=center>{h_lang_delivery_note}</td>
-->   <td width="8%" align=center>{h_lang_invoice}</td>
      <td width="8%" align=center>{h_lang_invoicelist}</td>
    </tr>
  </form>
  
<!-- BEGIN projects_list -->
      <tr bgcolor="{tr_color}">
        <td>{num}</td>
        <td>{customer}</td>
	<td>{title}</td>
        <td>{coordinator}</td>
        <td align=center>{status}</td>
        <td align=center>{end_date}</td>
<!--    <td align=center>{delivery_note}</td>
-->     <td align=center>{invoice}</td>
        <td align=center>{invoicelist}</td>
      </tr>
<!-- END projects_list -->

  </table>

<!-- link fuer alle invoices -->
      <table cellpadding=3 cellspacing=1>     
     <tr>
    <td><a href={all_invoicelist}>{lang_all_invoicelist}</a></td>
   </tr>
  </table>

</center>
