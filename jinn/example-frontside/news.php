<?php
	/*
	JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
	Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

	phpGroupWare - http://www.phpgroupware.org

	This file is part of JiNN

	JiNN is free software; you can redistribute it and/or modify it under
	the terms of the GNU General Public License as published by the Free
	Software Foundation; either version 2 of the License, or (at your 
	option) any later version.

	JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
	WARRANTY; without even the implied warranty of MERCHANTABILITY or 
	FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
	for more details.

	You should have received a copy of the GNU General Public License 
	along with JiNN; if not, write to the Free Software Foundation, Inc.,
	59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
	*/

	include("inc/config.php");
	include("inc/std_func.php");
?>
<html>
<head>
<title>I Dots Nieuws</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK REL="SHORTCUT ICON" HREF="img/favicon.ico">
<script language="JavaScript">
<!--
function MM_swapImgRestore() { //v3.0
var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.0
var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
	d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
	if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
//-->
</script>
<link rel="stylesheet" href="style/main.css" type="text/css">
</head>

<body  onLoad="MM_preloadImages('img/partners2.gif','img/diensten2.gif','img/portfolio2.gif','img/mydots2.gif','img/contact2.gif')">
<br>
<table width="100" border="0" cellspacing="2" cellpadding="0" bgcolor="#FF6633" align="center">
<tr> 
<td align="center" valign="middle"> 
<table width="720" border="0" cellspacing="0" cellpadding="0" height="80%">
<tr> 
<td width="12" nowrap background="img/zijtab.gif">&nbsp;</td>
<td bgcolor="#FFFFFF" valign="top" nowrap> 
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="50">
<tr> 
<td height="30" valign="top" align="center"> <img src="img/idotslogo1.gif" width="122" height="30"></td>
</tr>
<tr> 
<td height="20" align="center"> <a href="home.php" target="_self"><img name="button-home" border="0" src="img/home1.gif" width="47" height="12"></a><img src="img/nieuws2.gif" width="57" height="12" border="0"><a href="partners.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('button-partners1','','img/partners2.gif',1)" target="_self"><img name="button-partners1" border="0" src="img/partners1.gif" width="61" height="12"></a><a href="diensten.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('button-diensten1','','img/diensten2.gif',1)" target="_self"><img name="button-diensten1" border="0" src="img/diensten1.gif" width="59" height="12"></a><a href="portfolio.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('button-portfolio1','','img/portfolio2.gif',1)" target="_self"><img name="button-portfolio1" border="0" src="img/portfolio1.gif" width="64" height="12"></a><a href="mydots.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('button-mydots1','','img/mydots2.gif',1)" target="_self"><img name="button-mydots1" border="0" src="img/mydots1.gif" width="60" height="12"></a><a href="contact.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('button-contact1','','img/contact2.gif',1)" target="_self"><img name="button-contact1" border="0" src="img/contact1.gif" width="57" height="12"></a></td>
</tr>
</table>
<br>
<br>
<table width="660" border="0" cellspacing="0" cellpadding="0" align="center">
<tr> 
<td width="140" align="left" valign="top">&nbsp;</td>
<td align="left" valign="top"> 
<?php
	$news=makeNewsDetail($id);
	if ($news)
	{
		echo $news;
	}
?>
</td>
<td width="140" valign="bottom" align="right">&nbsp;</td>
</tr>
<tr align="center"> 
<td colspan="3" height="41" class="textsmall"> MAASDIJK 4&nbsp;&nbsp;&nbsp; 
5328 BG&nbsp;&nbsp; ROSSUM &nbsp;&nbsp;&nbsp;&nbsp;T&nbsp; 0418 
663963 &nbsp;&nbsp;&nbsp;F&nbsp; 0418 663053</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<div align="center"><br>
<span class="whitetext">&copy; I DOTS 2002</span></div>
</body>
</html>
