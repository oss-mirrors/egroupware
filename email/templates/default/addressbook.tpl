<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<HTML LANG="en">
<head>
<title>{title}</title>
<meta http-equiv="content-type" content="text/html; charset="{charset}">
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
<p>{lang_addressbook_action}<br>
<hr noshade width="98%" align="center" size="1">

 {total_matchs}
 {next_matchs}
	
  <table width=100% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="30%" bgcolor="{th_bg}" align=center>{sort_firstname}</td>
      <td width="30%" bgcolor="{th_bg}" align=center>{sort_lastname}</td>
      <td width="35%" bgcolor="{th_bg}" align=center>{lang_email}</td>
    </tr>
  </form>
  
<!-- BEGIN addressbook_list -->
      <tr bgcolor="{tr_color}">
	<td width=30%>{firstname}</td>
        <td width=30%>{lastname}</td>
	<form>
        <td align=center width="35%"><input type="text" size="25" name="email" value="{email}">
	<input type="button" size="25" name="button" value="To" onClick="ExchangeTo(this.form);">
        <input type="button" size="25" name="button" value="Cc" onClick="ExchangeCc(this.form);"></td>
      </form>    
      </tr>
<!-- END addressbook_list -->

  </table>
  <table cellpadding=3 cellspacing=1>
      <tr> 
    <form>  
    <td><input type="button" name="Done" value="{lang_done}" onClick="window.close()">
      </form>
      </td>
    </tr>
  </table>
</center>
</body>
</html>