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

<table border="0" width="100%" cellspacing="2" cellpadding="2">
 <tr>
  <td colspan="4" align="left">
   <table border="0" width="100%">
    <tr>
    {left}
    <td align="center">{lang_showing}</td>
    {right}
    </tr>
   </table>
   </td>
  </tr>
 <tr>
  <td>&nbsp;</td>
  <td colspan="4" align=right>
  <form method="post" action="{searchurl}">
  <input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}">
  </form></td>
 </tr>

    <tr bgcolor="{th_bg}">
      <td width="20%" bgcolor="{th_bg}" align=center><font face="{font}">{sort_firstname}</font></td>
      <td width="20%" bgcolor="{th_bg}" align=center><font face="{font}">{sort_lastname}</font></td>
      <td width="20%" bgcolor="{th_bg}" align=center><font face="{font}">{sort_etype}</font></td>
      <td width="40%" bgcolor="{th_bg}" align=center><font face="{font}">{lang_email}</font></td>
    </tr>
  
<!-- BEGIN addressbook_list -->
      <tr bgcolor="{tr_color}">
	<td><font face="{font}">{firstname}</font></td>
        <td><font face="{font}">{lastname}</font></td>
        <td align="center"><font face="{font}">{etype}</font></td>
	<form>
        <td align="center"><font face="{font}"><input type="text" size="25" name="email" value="{email}">
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