<!-- BEGIN form -->
<p><b>{lang_action}</b><hr><br>
{error_messages}

 <form method="POST" action="{form_action}">
  <center>
   <table border=0 width=85%>
    <tr bgcolor="{th_bg}">
      <td colspan="4">&nbsp;</td>
    </tr>

    <tr bgcolor="{tr_color1}">
     <td width="25%">{lang_loginid}</td>
     <td width="25%">{account_lid}&nbsp;</td>

     <td width="25%">{lang_account_active}:</td>
     <td width="25%">{account_status}</td>
    </tr>

    <tr bgcolor="{tr_color2}">
     <td>{lang_firstname}</td>
     <td>{account_firstname}&nbsp;</td>
     <td>{lang_lastname}</td>
     <td>{account_lastname}&nbsp;</td>
    </tr>

    {password_fields}
 
    <tr bgcolor="{tr_color2}">
     <td>{lang_groups}</td>
     <td colspan=3>{groups_select}&nbsp;</td>
    </tr>

    <tr bgcolor="{tr_color1}">
     <td>{lang_expires}</td>
     <td colspan=3>{input_expires}&nbsp;</td>
    </tr>

    {permissions_list}
    
    {gui_hooks}

	 {form_buttons}

   </table>
  </center>
 </form>
<!-- END form -->

<!-- BEGIN form_passwordinfo -->
    <tr bgcolor="{tr_color1}">
     <td>{lang_password}</td>
     <td><input type="password" name="account_passwd" value="{account_passwd}"></td>
     <td>{lang_reenter_password}</td>
     <td><input type="password" name="account_passwd_2" value="{account_passwd_2}"></td>
    </tr>
<!-- END form_passwordinfo -->

<!-- BEGIN form_buttons_ -->
    <tr bgcolor="{tr_color2}">
     <td colspan="4" align="right"><input type="submit" name="submit" value="{lang_button}"></td>
    </tr>
<!-- END form_buttons_ -->

<!-- BEGIN form_logininfo -->
    <tr bgcolor="{tr_color1}">
     <td>{lang_lastlogin}</td>
     <td>{account_lastlogin}</td>

     <td>{lang_lastloginfrom}</td>
     <td>{account_lastloginfrom}</td>
    </tr>
<!-- END form_logininfo -->
