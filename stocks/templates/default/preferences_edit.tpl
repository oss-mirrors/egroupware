<!-- $Id$ -->

<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
      <hr noshade width="98%" align="center" size="1">

      <center>
       <form method="POST" name="preferences_edit" action="{actionurl}">
         {common_hidden_vars}
        <table border="0" cellspacing="1" cellpadding="3">
          <tr bgcolor="{th_bg}">
          <td colspan=2 align=center>{h_lang_edit}</td>
         </tr>
         <tr bgcolor="{tr_color}">
          <td align=right>{lang_symbol}</td>
          <td><input type="text" name="symbol" value="{symbol}"></td>
         </tr>
         <tr bgcolor="{tr_color}">
         <td align=right>{lang_company}</td> 
	 <td><input type="text" name="name" value="{name}"></td>
         </tr>
    
     <!-- BEGIN edit -->
         <tr valign="bottom">
          <td colspan=2 align=center>
           <input type="submit" name="edit" value="{lang_edit}">
          </td>
         </tr>
         </table>
         </form>
         </center>
         </html>
         
        <!-- END edit -->
        

