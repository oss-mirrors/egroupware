<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>bookmarker: {TITLE}</title>
<!-- $Id$ -->

<style>
<!--
 html,body { background-color: white;  
             font-family: "Arial, Helvetica"; }

 a:LINK    { color: blue; }
 a:VISITED { color: #000066; }
 a:ACTIVE  { color: red; }
 a:HOVER   { color: black; }

 table.hdr { background-color: #5B68A6;
             color: white; }
 big.hdr    { color: white; }
 small.hdr  { color: #CCCCCC; }

 a.hdr:LINK    { color: #CCCCCC; }
 a.hdr:VISITED { color: #CCCCCC; }
 a.hdr:ACTIVE  { color: #CCCCCC; }
 a.hdr:HOVER   { color: #000000; }

-->
</style>

</head>

<body bgcolor="#FFFFFF">

<table width=95% border=0 cellspacing=0 bgcolor=#CCCCCC class=hdr>
 <tr>
  <td valign="top"    align="left"><strong><big class=hdr>{TITLE}</big></strong></td>
  <td valign="bottom" align="right">
   &nbsp;
  </td>
 </tr>
</table>

<table width=95% border=0 cellspacing=0>
{MESSAGES}
 <tr>
  <td>    

{BODY}

  </td>
 </tr>
 <tr>
   <td align=right valign=bottom><hr>
   <small>bookmarker {VERSION} at {SERVER_NAME} {NAME_HTML}</small>
   </td>
 </tr>
</table>
</body>
</html>
