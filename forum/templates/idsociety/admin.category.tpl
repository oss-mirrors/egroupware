
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
                <input type="hidden" name="cat_id" value="{CAT_ID}">
                <input type="hidden" name="action" value="{ACTIONTYPE}">
              </center>
            </td>
          </tr>
          <tr> 
            <td>{LANG_CAT_NAME}</td>
            <td> 
              <input type="text" name="catname" size=40 maxlength=49 value="{CAT_NAME}">
            </td>
          </tr>
          <tr> 
            <td>{LANG_CAT_DESC}</td>
            <td> 
              <textarea rows="3" cols="40" name="catdescr" virtual-wrap maxlength=240>{CAT_DESC}</textarea>
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
<br>
<p>&nbsp;</p>
