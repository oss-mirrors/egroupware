<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                     
<hr noshade width="98%" align="center" size="1">                                                                                                                                                  
<center>
 
<form method="POST" action="{actionurl}">

<table border=0 cellspacing=1 cellpadding=3>
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
  {total_matchs}
  {next_matchs}

<table width=100% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="3%" bgcolor="{th_bg}" align=center>{h_lang_select}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_activity}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_remark}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_date}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_aes}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_billperae}</td>
      <td width="7%" bgcolor="{th_bg}" align=center>{sort_sum}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{h_lang_edithour}</td>
    </tr>
<!-- BEGIN projecthours_list -->
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
<!-- END projecthours_list -->

      <tr bgcolor="{tr_color}">
        <td>
        </td>
        <td align=center>
         <font size="4"><b>{title_netto}</b></font></td>
        <td></td>
        <td></td>
        <td align=right>
         <font size="4"><b>{sum_aes}</b></font>
        </td>
        <td>
        </td>
        <td align=right>
         <font size="4"><b>{sum_sum}</b></font></td>
        <td></td>
      </tr>
</table>
<table border=0 cellpadding=3 cellspacing=1>
<tr>
<td><input type="submit" name="Update" value="{lang_update}"></td>
<td><input type="submit" name="Invoice" value="{lang_createinvoice}"></td>
  </form>
<!-- url zum druck -->
<td><a href={print_invoice} target=_blank>{lang_print_invoice}</a></td>
</tr>
</table>
</center>
