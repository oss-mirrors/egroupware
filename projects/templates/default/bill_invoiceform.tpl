<!-- $Id$ -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<HTML LANG="en">
<head>
<title>{site_title}</title>
<meta http-equiv="content-type" content="text/html; charset={charset}">
<STYLE type="text/css">
   A {text-decoration:none;}
   <!--
   A:link {text-decoration:none;}
   A:visted {text-decoration:none;}
   A:active {text-decoration:none;}
   body {margin-top: 0px; margin-right: 0px; margin-left: 0px;}
   td {text-decoration:none;}
   tr {text-decoration:none;}
   table {text-decoration:none;}
   center {text-decoration:none;}
   -->
</STYLE>
</head>
<body bgcolor="#FFFFFF">
	<table width=90% border="0" cellpadding="3" cellspacing="3">
		<tr>
			<td valign="bottom">{myaddress}</td>
			<td align="right"><img src="{img_src}"></td>
		</tr>
		<tr>
			<td height="2">&nbsp;</td>
		</tr>
		<tr>
		<tr>
			<td>{customer}</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td height="2">&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{lang_invoice_num}:&nbsp;{invoice_num}</font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{lang_invoice_date}:&nbsp;{invoice_date}</font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{lang_project}:&nbsp;{title}</font></td>
			<td>&nbsp;</td>
		</tr>
	</table><br><br><br>  
	<table width="90%" border="0" cellspacing="3" cellpadding="3">
		<tr>
      		<td width="8%" align="right"><font face="{font}">{lang_position}</font></td>
			<td width="10%" align="center"><font face="{font}">{lang_work_date}</font></td>
			<td width="30%"><font face="{font}">{lang_descr}</font></td>
			<td width="10%" align="right"><font face="{font}">{lang_workunits}</font></td>
			<td width="15%" align="right"><font face="{font}">{currency}&nbsp;{lang_per}</font></td>
			<td width="10%" align="right"><font face="{font}">{currency}&nbsp;{lang_sum}</font></td>
		</tr>

<!-- BEGIN bill_list -->

		<tr>
			<td align="right"><font face="{font}">{pos}</font></td>
			<td align="center"><font face="{font}">{hours_date}</font></td>
			<td><font face="{font}">{act_descr}</font></td>
			<td align="right"><font face="{font}">{aes}</font></td>
			<td align="right"><font face="{font}">{billperae}</font></td>
			<td align="right"><font face="{font}">{sumpos}</font></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><font face="{font}">{hours_descr}</font></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>

<!-- END bill_list -->

	</table><br><br>
	<table width="90%" border="0" cellspacing="3" cellpadding="3">
		<tr>
			<td width="8%">&nbsp;</td>
			<td width="10%">&nbsp;</td>
			<td width="10%">&nbsp;</td>
			<td width="30%">&nbsp;</td>
			<td width="10%"><font face="{font}">{currency}&nbsp;{lang_netto}:</font></td>
			<td width="10%" align="right"><font face="{font}">{sum_netto}</font></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><font face="{font}">{currency}&nbsp;{tax}&nbsp;%&nbsp;{lang_tax}:</font></td>
			<td align="right"><font face="{font}">{sum_tax}</font></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><font face="{font}" size=4><b>{currency}&nbsp;{lang_sum}:</b></font></td>
			<td align="right"><font face="{font}" size="4"><b>{sum_sum}</b></font></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><font face="{font}">{message}</font></td>
			<td><font face="{font}">{error_hint}</font></td>
		</tr>
		<hr noshade width="90%" align="left" size="1"> 
	</table>
</body>
</html>
