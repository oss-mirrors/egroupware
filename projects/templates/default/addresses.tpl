<!-- $Id$ -->
<html>
<head>
<title>{title}</title>
<meta http-equiv="content-type" content="text/html"; charset="{charset}">
<link rel="stylesheet" href="css/style.css">
<script LANGUAGE="JavaScript">
   function ExchangeAddress(thisform)
   {
   opener.document.prefs_form.address.value = thisform.elements[0].value;
   opener.document.prefs_form.addressname.value = thisform.elements[1].value;
   }
</script>
</head>
<body bgcolor="{bg_color}">   
<center>
<p><b>{lang_address_action}</b><br>
<hr noshade width="98%" align="center" size="1">
 {total_matchs}
 {next_matchs}
  
  <table width=100% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="30%" bgcolor="{th_bg}" align=center>{sort_company}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_firstname}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_lastname}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{lang_address}</td>
    </tr>
  </form>
<!-- BEGIN address_list -->
      <tr bgcolor="{tr_color}">
        <td>{company}</td>
	<td>{firstname}</td>
        <td>{lastname}</td>
	<form>
        <input type="hidden" size="25" name="hidden" value="{id}">
	<input type="hidden" size="25" name="hidden" value="{company}">
	<td align=center><input type="button" value="{lang_select_address}" onClick="ExchangeAddress(this.form);" name="button"></td>
      </form>    
      </tr>
<!-- END address_list -->

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