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
	<table width=70% border="0" cellpadding="3" cellspacing="3">
		<tr>
			<td valign="bottom"><font face="{font}">{ad_company}</font></td>
			<td align="right"><img src="doc/logo.jpg"></td>
		</tr>
		<tr>
			<td><font face="{font}">{ad_firstname}&nbsp;{ad_lastname}</font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{ad_street}</font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{ad_zip}&nbsp;{ad_city}</font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{ad_state}</font></font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{ad_country}</font><br><br><br></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{company}</font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{firstname}&nbsp;{lastname}</font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{street}</font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{zip}&nbsp;{city}</font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{state}</font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{country}<br><br><br></font></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><font face="{font}">{lang_invoice}:&nbsp;{invoice_num}</font></td>
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
	<table width="70%" border="0" cellspacing="3" cellpadding="3">
		<tr>
      		<td width="8%" align="right"><font face="{font}">{lang_pos}</font></td>
			<td width="10%" align="right"><font face="{font}">{lang_workunits}</font></td>
			<td width="10%" align="center"><font face="{font}">{lang_hours_date}</font></td>
			<td width="30%"><font face="{font}">{lang_descr}</font></td>
			<td width="10%" align="right"><font face="{font}">{currency}&nbsp;{lang_per}</font></td>
			<td width="10%" align="right"><font face="{font}">{currency}&nbsp;{lang_sum}</font></td>
		</tr>

<!-- BEGIN invoicepos_list -->

		<tr>
			<td align="right"><font face="{font}">{pos}</font></td>
			<td align="right"><font face="{font}">{aes}</font></td>
			<td align="center"><font face="{font}">{hours_date}</font></td>
			<td><font face="{font}">{act_descr}</font></td>
			<td align="right"><font face="{font}">{billperae}</font></td>
			<td align="right"><font face="{font}">{sumperpos}</font></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><font face="{font}">{hours_descr}</font></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>

<!-- END invoicepos_list -->

	</table><br><br>
	<table width="70%" border="0" cellspacing="3" cellpadding="3">
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
			<td><font face="{font}">{currency}&nbsp;{lang_mwst}&nbsp;%&nbsp;{tax_percent}:</font></td>
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
			<td><font face="{font}">{error}</font></td>
			<td><font face="{font}">{error_hint}</font></td>
			<td>&nbsp;</td>
		</tr>
		<hr noshade width="70%" align="left" size="1"> 
	</table>
</body>
</html>
