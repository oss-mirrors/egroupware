<!-- $Id$ -->
<html>
<head>
<title>{site_title}</title>
<meta http-equiv="content-type" content="text/html; charset={charset}">
<link rel="stylesheet" href="css/style.css">
</head>
<body bgcolor="#FFFFFF">
<table width=70% border=0 cellpadding=3 cellspacing=3>
<tr>
<td valign=bottom>{ad_company}</td>
<td align=right><img src="doc/logo.jpg"></td>
</tr>
<tr>
<td>{ad_firstname}&nbsp;{ad_lastname}</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>{ad_street}</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>{ad_zip}&nbsp;{ad_city}</td>
<td>&nbsp;</td>
</tr>
<td>{ad_state}<br><br><br></td>
<td>&nbsp;</td>
</tr>
<tr>
<td>{company}</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>{firstname}&nbsp;{lastname}</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>{street}</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>{zip}&nbsp;{city}</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>{state}<br><br><br></td>
<td>&nbsp;</td>
</tr>
<tr>
<td>{lang_invoice}:&nbsp;{invoice_num}</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>{lang_date}:&nbsp;{invoice_day}.{invoice_month}.{invoice_year}</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>{lang_project}:&nbsp;{title}</td>
<td>&nbsp;</td>
</tr>
</table><br><br><br>  
<table width=70% border=0 cellspacing=3 cellpadding=3>
    <tr>
      <td width="8%" align=right>{lang_pos}</td>
      <td width="10%" align=right>{lang_workunits}</td>
      <td width="10%" align=center>{lang_date}</td>
      <td width="30%">{lang_descr}</td>
      <td width="10%" align=right>{currency}&nbsp;a</td>
      <td width="10%" align=right>{currency}&nbsp;Sum</td>
    </tr>

<!-- BEGIN invoicepos_list -->
      <tr>
        <td align=right>{pos}</td>
        <td align=right>{aes}</td>
        <td align=center>{day}.{month}.{year}</td>
        <td><b>{act_descr}</b></td>
        <td align=right>{billperae}</td>
        <td align=right>{sumperpos}</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>{act_remark}</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>

<!-- END invoicepos_list -->

  <hr noshade width="70%" align="left" size="1">
      <tr>
        <td width=8%>&nbsp;</td>
        <td width=10%>&nbsp;</td>
        <td width=10%>&nbsp;</td>
        <td width=30%>&nbsp;</td>
        <td width=10%>{currency}&nbsp;{lang_netto}:</td>
        <td width=10% align=right>{sum_netto}</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>{currency}&nbsp;{lang_mwst}&nbsp;%&nbsp;{tax_percent}:</td>
        <td align=right>{sum_tax}</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td><font size=4><b>{currency}&nbsp;Sum:</b></font></td>
        <td align=right><font size="4"><b>{sum_sum}</b></font></td>
      </tr>
          <tr>                                                                                                                                                      
        <td>&nbsp;</td>                                                                                                                                         
        <td>&nbsp;</td>                                                                                                                                         
        <td>&nbsp;</td>                                                                                                                                         
        <td>{error}</td>                                                                                                                                         
        <td>{error_hint}</td>                                                                                                
        <td>&nbsp;</td>                                                                                             
      </tr>
   </table>
</body>
</html>
