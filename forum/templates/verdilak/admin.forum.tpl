
<table border="0" width=90% align="center">
  <tr> 
    <td bgcolor="{TABLEBG}" align="left">{FORUM_ADMIN}</td>
  </tr>
  <tr> 
    <td bgcolor="{TABLEBG}" align="left">[<font size=-1><a href="{CAT_LINK}">{LANG_CAT}</a>|<a href="{FOR_LINK}">{LANG_FOR}</a> 
      | <a href="{ADM_LINK}">{LANG_ADM_MAIN}</a> | <a href="{MAIN_LINK}">{LANG_MAIN}</a></font>]</td>
  </tr>
  <tr> 
    <td> <font size=-1> </font><br>
      <form method="post" action="{ACTION_LINK}">
        <table border="0" width=80% bgcolor="9999FF">
          <tr> 
            <td colspan=2 bgcolor="D3DCE3"> 
              <center>
                {LANG_ADD_CAT} 
                <input type="hidden" name="for_id" value="{FORID}">
              </center>
            </td>
          </tr>
          <tr> 
            <input type="hidden" name="action" value="{ACTION}">
            <td>{BELONG_TO}</td>
            <td> 
              <select name="goestocat">
 <!-- BEGIN DropDown --> 
             {DROP_DOWN}             
  
<!-- END DropDown --> 
              </select>
            </td>
          <tr> 
            <td>{LANG_FORUM}</td>
            <td> 
              <input type="text" name="forname" size=40 maxlength=49 value="{FORUM_NAME}">
            </td>
          </tr>
          <tr> 
            <td>{LANG_FORUM_DESC}</td>
            <td> 
              <textarea rows="3" cols="40" name="fordescr" virtual-wrap maxlength=240>{FOR_DESC}</textarea>
            </td>
          </tr>
          <tr> 
            <td colspan=2 align=right> 
              <input type="submit" value="{BUTTONLANG}" name="submit">
            </td>
          </tr>
        </table>
      </form>
      <br>
      <center>
      </center>
    </td>
  </tr>
</table>
<p>&nbsp;</p>
