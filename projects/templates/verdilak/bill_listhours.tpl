<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                     
<hr noshade width="98%" align="center" size="1">                                                                                                                                                  
<center>
{error} 
<form method="POST" action="{actionurl}">
<table border=0 cellspacing=1 cellpadding=3>
<tr>
<td>{lang_choose}</td>
<td>{choose}</td>
</tr>
<tr>
<td>{title_invoice_num} :</td>
<td><input type=text name="invoice_num" value="{invoice_num}">{invoice_hint}</td>
</tr>
<tr>
<td>{title_customer} :</td>
<td>{customer}</td>
</tr>
<tr>
<td>{title_project} :</td>
<td>{project}</td>
</tr>
<tr>
<td>{lang_invoice_date} :</td>
<td>{date_formatorder} {date_hint}</td>
</tr>
</table> 
 {common_hidden_vars}
<table width=100% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="3%" bgcolor="{th_bg}" align=center>{h_lang_select}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_activity}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_remark}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_date}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_aes}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{currency}&nbsp;{sort_billperae}</td>
      <td width="7%" bgcolor="{th_bg}" align=right>{currency}&nbsp;{sort_sum}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{h_lang_edithour}</td>
    </tr>

<!-- BEGIN invoicehours_list -->

      <tr bgcolor="{tr_color}">
        <td align=center>{select}</td>
        <td>{activity}</td>
        <td>{remark}</td>
        <td align=center>{status}</td>
        <td align=center>{date}</td>
        <td align=right>{aes}</td>
        <td align=right>{billperae}</td>
        <td align=right>{sum}</td>
        <td align=center>{edithour}</td>
      </tr>

<!-- END invoicehours_list -->

</table><br><br>
<table width=100% border=0 cellspacing=0 cellpadding=0>
      <tr bgcolor="{tr_color}">
        <td width="3%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%" align=center><font size="4"><b>{currency}&nbsp;{title_netto}</b></font></td>
        <td width="10%" align=right><font size="4"><b>{sum_aes}</b></font></td>
        <td width="10%">&nbsp;</td>
        <td width="7%" align=right><font size="4"><b>{sum_sum}</b></font></td>
        <td width="10%">&nbsp;</td>
      </tr>
</table>
<table width="50%" border=0 cellpadding="2" cellspacing="2">
<tr>
<td align="center"><input type="submit" name="Invoice" value="{lang_invoice}"></td>
  </form>
<!-- url zum druck -->
<td align="center"><a href={print_invoice} target=_blank>{lang_print_invoice}</a></td>
</tr>
</table>
</center>
