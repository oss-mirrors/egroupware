<center><font size="+2">{module_name}</font></center>
<table border=0 width="95%" align=center>
   <tr><td>
      <tr width="100%"><td align=center>{misc_data}</td></tr>
   </tr></td>
   <tr><td>
      <table border=0 width="95%" align=center>
         <tr bgcolor="{em_bgcolor}">
            <td valign=center>{home_link}<font color="{em_text_color}">{ftp_location}</font></td>
            <td align=right><font color="{em_text_color}">{relogin_link}</font></td>
         </tr>
      </table>
   </td></tr>
   <tr><td>
      <table border=0 cellpadding=1 cellspacing=1 width=95% align=center>
         {rowlist_1}
         {rowlist_2}
      </table>
   </td></tr>
   <tr bgcolor="{em_bgcolor}" width="95%">
      <td align="center" bgcolor="{em_bgcolor}"><font color="{em_text_color}">
         {ul_form_open}
         {ul_select}{ul_submit}
         {ul_form_close}
      </font></td>
   </tr>
   <tr bgcolor="{em_bgcolor}" width="95%">
      <td align="center"><font color="{em_text_color}">
         {crdir_form_open}
         {crdir_textfield}{crdir_submit}
         {crdir_form_close}
      </font></td>
   </tr>
</table>
