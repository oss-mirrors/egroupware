<!-- BEGIN list_header -->
<br>{lang_showing}
<br>{searchreturn}
{search_filter}
<table width="75%" border="0" cellspacing="1" cellpadding="3">
<tr bgcolor="{th_bg}">
  <td width="5%" height="21"><font face="Arial, Helvetica, sans-serif" size="-1">{lang_edit}</font></td>
  <td width="5%" height="21"><font face="Arial, Helvetica, sans-serif" size="-1">{lang_del}</font></td>
  {cols}
</tr>
<!-- END list_header -->

<!-- BEGIN column -->
  <td valign="top"><font face="Arial, Helvetica, san-serif" size="2">{col_data}&nbsp;</font></td>
<!-- END column -->

<!-- BEGIN row -->
<tr bgcolor="{row_tr_color}">
  <td valign="top" width="5%"><font face="{font}" size="2">{row_edit}</font></td>
  <td valign="top" width="5%"><font face="{font}" size="2">{row_del}</font></td>
{columns}
</tr>
<!-- END row -->

<!-- BEGIN list_footer --> 
 </table>
 <table width="75%" border="0" cellspacing="0" cellpadding="4">
   <tr bgcolor="{th_bg}"> 
     <form action="{add_url}"    method="post"><td width="16%"><input type="submit" name="Add"      value="{lang_add}"></td></form>
     <form action="{vcard_url}"  method="post"><td width="16%"><input type="submit" name="AddVcard" value="{lang_addvcard}"></td></form>
     <form action="{import_url}" method="post"><td width="16%"><input type="submit" name="Import"   value="{lang_import}"></td></form>
     <form action="{import_alt_url}" method="post"><td width="16%"><input type="submit" name="Import" value="{lang_import_alt}"></td></form>
     <form action="{export_url}" method="post"><td width="16%"><input type="submit" name="Export"   value="{lang_export}"></td></form>
   </tr>
 </table>
 </center>
<!-- END list_footer -->
