<!-- $Id$ -->
   <p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
      <hr noshade width="98%" align="center" size="1">
      <center>
       {error}
        <form method="POST" name="activity_form" action="{actionurl}">
         {hidden_vars}
	    {error}{message}
          <table width="75%" border="0" cellspacing="1" cellpadding="3">
         <tr>
          <td>{lang_choose}</td>
          <td>{choose}</td>
         </tr>
	<tr>
          <td>{lang_num}:</td>
          <td><input type="text" name="num" value="{num}"></td>
         </tr>
         <tr>
          <td>{lang_descr}:</td>
          <td colspan="2"><textarea name="descr" rows=4 cols=50 wrap="VIRTUAL">{descr}</textarea></td>
         </tr>
         <tr>
          <td>{lang_remarkreq}:</td>
          <td><select name="remarkreq">{remarkreq_list}</select></td>
         </tr>
         <tr>
          <td>{currency}&nbsp;{lang_billperae}:</td>
          <td><input type="text" name="billperae" value="{billperae}"></td>
         </tr>
         <tr>
          <td>{lang_minperae}:</td>
          <td><input type="text" name="minperae" value="{minperae}"></td>
         </tr>
         </table>
         
         <!-- BEGIN add -->
         
         <table width="75%" border="0" cellspacing="1" cellpadding="3">
         <tr valign="bottom">
          <td height="62"><input type="submit" name="submit" value="{lang_add}"></td>
          <td height="62"><input type="reset" name="reset" value="{lang_reset}"></td>
         </tr>
         </table>
         </form>
         </center>
         
        <!-- END add -->
        
        <!-- BEGIN edit -->
         
         <table width="75%" border="0" cellspacing="1" cellpadding="3">
         <tr valign="bottom">
          <td height="62"><input type="submit" name="submit" value="{lang_edit}">&nbsp;</form></td>
          <td height="62"><form method="POST" action="{deleteurl}">{hidden_vars}
            <input type="submit" name="delete" value="{lang_delete}"></form></td>
         </tr>
         </table>
	</center>

        <!-- END edit -->
