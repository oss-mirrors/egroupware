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
<center>
<table width="70%" border="0" cellpadding="3" cellspacing="3">
	<tr>
		<td valign="bottom">{myaddress}</td>
		<td align="right"><img src="doc/logo.jpg"></td>
	</tr>
	<tr>
		<td height="2">&nbsp;</td>
	</tr>
	<tr>
		<td>{customer}</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td height="2">&nbsp;</td>
	</tr>
	<tr>
		<td><font face="{font}">{lang_delivery_num}:&nbsp;{delivery_num}</font></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><font face="{font}">{lang_delivery_date}:&nbsp;{delivery_date}</font></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><font face="{font}">{lang_project}:&nbsp;{title}</font></td>
		<td>&nbsp;</td>
	</tr>
</table><br><br><br>
<table width="70%" border="0" cellspacing="3" cellpadding="3">
	<tr>
		<td width="5%" align="right"><font face="{font}">{lang_position}</font></td>
		<td width="30%"><font face="{font}">{lang_descr}</font></td>
		<td width="10%" align="center"><font face="{font}">{lang_work_date}</font></td>
		<td width="10%" align="right"><font face="{font}">{lang_workunits}</font></td>
	</tr>

<!-- BEGIN del_list -->

	<tr>
		<td align="right"><font face="{font}">{pos}</font></td>
		<td><font face="{font}">{act_descr}</font></td>
		<td align="center"><font face="{font}">{hours_date}</font></td>
		<td align="right"><font face="{font}">{aes}</font></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><font face="{font}">{hours_descr}</font></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>

<!-- END del_list -->

</table><br><br>
<table width="70%" border="0" cellspacing="3" cellpadding="3">
	<tr>
		<td width="5%">&nbsp;</td>
		<td width="30%"><font face="{font}" size="4"><b>{lang_sumaes}</b></font></td>
		<td width="10%">&nbsp;</td>
		<td width="10%" align="right"><font face="{font}" size="4"><b>{sumaes}</b></font></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><font face="{font}">{message}</font></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<hr noshade width="70%" size="1">
</table>
</center>
</body>
</html>
