<!-- $Id$ -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<HTML LANG="en">
<head>
<title>{title}</title>
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
<script LANGUAGE="JavaScript">
   function ExchangeTo(thisform)
   {
    if (opener.document.doit.to.value =='') {
   opener.document.doit.to.value = thisform.elements[0].value;
   }
  else {
   opener.document.doit.to.value +=","+thisform.elements[0].value;
  }
}
function ExchangeCc(thisform)
   {
     if (opener.document.doit.cc.value=='') {
        opener.document.doit.cc.value=thisform.elements[0].value;
     } else {
        opener.document.doit.cc.value+=","+thisform.elements[0].value;
     }
   }
</script>
</head>
<body bgcolor="{bg_color}">
<center>
<p><font face="{font}"><b>{lang_addressbook_action}</b></font><br>
<hr noshade width="98%" align="center" size="1">

<table border="0" width="100%">
    <tr>
    <td width="33%" align="left">
    <form action="{cats_action}" name="form" method="POST">
    <select name="cat_id" onChange="this.form.submit();"><option value="">{lang_select_cats}</option>{cats_list}</select>
    <noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript></form></td>
    <td width="33%" align="center">{lang_showing}</td>
    <td width="33%" align="right">
    <form method="POST" action="{search_action}">
    <input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}">
    </form></td>
    </tr>
    <tr>
    <td colspan="4">
    <table border="0" width="100%">
    <tr>
    {left}
    <td>&nbsp;</td>
    {right}
    </tr>
    </table>
    </td>
    </tr>
</table>
<table border="0" width="100%" cellpadding="2" cellspacing="2">
    <tr bgcolor="{th_bg}">
      <td width="15%" bgcolor="{th_bg}" align=center><font face="{font}">{sort_firstname}</font></td>
      <td width="15%" bgcolor="{th_bg}" align=center><font face="{font}">{sort_lastname}</font></td>
      <td width="25%" bgcolor="{th_bg}" align=center><font face="{font}">{lang_email}</font></td>
      <td width="25%" bgcolor="{th_bg}" align=center><font face="{font}">{lang_hemail}</font></td>
    </tr>
  
<!-- BEGIN addressbook_list -->
      <tr bgcolor="{tr_color}">
	<td><font face="{font}">{firstname}</font></td>
        <td><font face="{font}">{lastname}</font></td>
        <form>
        <td align="center"><font face="{font}"><input type="text" size="25" name="email" value="{email}">
        <input type="button" size="25" name="button" value="To" onClick="ExchangeTo(this.form);">
        <input type="button" size="25" name="button" value="Cc" onClick="ExchangeCc(this.form);"></font></td>
        </form>
        <form>
        <td align="center"><font face="{font}"><input type="text" size="25" name="hemail" value="{hemail}">
        <input type="button" size="25" name="button" value="To" onClick="ExchangeTo(this.form);">
        <input type="button" size="25" name="button" value="Cc" onClick="ExchangeCc(this.form);"></font></td>
        </form>
      </tr>
<!-- END addressbook_list -->

  </table>
  <table cellpadding="2" cellspacing="2">
      <tr>
    <form>
    <td><font face="{font}"><input type="button" name="done" value="{lang_done}" onClick="window.close()"></font>
      </form>
      </td>
    </tr>
  </table>
</center>
</body>
</html>
