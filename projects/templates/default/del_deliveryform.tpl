<html>
<head>
<title>{site_title}</title>
<meta http-equiv="content-type" content="text/html; charset={charset}">
<link rel="stylesheet" href="css/style.css">
</head>
<body bgcolor="#FFFFFF">
<table width=70% border=0 cellpadding=3 cellspacing=3>
<tr>
<td><img src="doc/logo.gif"><br><br><br></td>
</tr>
<tr>
<td>{company}</td>
</tr>
<tr>
<td>{firstname}&nbsp;{lastname}</td>
</tr>
<tr>
<td>{street}</td>
</tr>
<tr>
<td>{zip}&nbsp;{city}<br><br><br></td>
</tr>
<tr>
<td>{lang_delivery}:&nbsp;{delivery_num}</td>
</tr>
<tr>
<td>{lang_date}:&nbsp;{delivery_day}.{delivery_month}.{delivery_year}</td>
</tr>
<tr>
<td>{lang_project}:&nbsp;{title}</td>
</tr>
</table><br><br><br>  
<table width=70% border=0 cellspacing=3 cellpadding=3>
    <tr>
      <td width="8%" align=right>{lang_pos}</td>
      <td width="10%" align=right>{lang_workunits}</td>
      <td width="10%" align=center>{lang_date}</td>
      <td width="30%">{lang_descr}</td>
    </tr>

<!-- BEGIN deliverypos_list -->
      <tr>
        <td align=right>{pos}</td>
        <td align=right>{aes}</td>
        <td align=center>{day}.{month}.{year}</td>
        <td><b>{act_descr}</b></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>{act_remark}</td>
      </tr>

<!-- END deliverypos_list -->

  <hr noshade width="70%" align="left" size="1">
    </table>
</body>
</html>
