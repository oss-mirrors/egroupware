
<p>&nbsp;</p>
<table align=center bgcolor=#000000 border=0 cellpadding=0 cellspacing=0 
width="80%">
  <tbody> 
  <tr> 
    <td> 
      <table align=center border=0 cellpadding=0 cellspacing=1 width="100%">
        <tr> 
          <td colspan="2" bgcolor="#FFFFFF"> 
            <table border="0" width=100% align="center" cellspacing="1" cellpadding="0">
              <tr> 
                <td bgcolor="{TABLEBG}" align="left">{FORUM_ADMIN}</td>
              </tr>
              <tr> 
                <td bgcolor="{TABLEBG}" align="left">[<font size=-1><a href="{CAT_LINK}">{LANG_CAT}</a>|<a href="{FOR_LINK}">{LANG_FOR}</a> 
                  | <a href="{MAIN_LINK}">{LANG_MAIN}</a></font>]</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr bgcolor="#{TABLEBG}"> 
          <td colspan="2"> 
            <div align="center">{LANG_CURRENT_SUBFORUM} </div>
          </td>
        </tr>
        <tbody> 
 <!-- BEGIN CatBlock -->
        <tr> 
          <td bgcolor="#{THBG}" width="69%" height="37"> <font color=#ffffff>{LANG_CATEGORY}</font></td>
          <td bgcolor="#{THBG}" width="31%" height="37"> 
            <blockquote> <font color=#ffffff>{LANG_ACTION}</font></blockquote>
          </td>
        </tr>


        <tr> 
          <td bgcolor="#{TABLEBG}" width="69%">{CAT_NAME}</td>
          <td rowspan="2" bgcolor="#{TABLEBG}" width="31%"> 
            <div align="center"><a href="{EDIT_LINK}"><font size="2">{LANG_EDIT}</font></a><font size="2">| 
              <a href="{DEL_LINK}">{LANG_DEL}</a></font></div>
          </td>
        </tr>
        <tr> 
          <td bgcolor="#{TABLEBG}" width="69%"><font size="2">{CAT_DESC}</font></td>
        </tr>
        <tr> 
          <td bgcolor=#{THBG} colspan="2"><font color=#ffffff face=Verdana size=1>{LANG_FORUM}</font></td>
        </tr>
        <!-- BEGIN ForumBlock --> 
        <tr> 
          <td bgcolor="#ffffff" width="69%">{SUBCAT_NAME} </td>
          <td bgcolor=#ffffff width="31%" rowspan="2"> 
            <div align="center"><font size=2><a href="{SUBEDIT_LINK}">{LANG_EDIT}</a> 
              | <a href="{SUBDEL_LINK}">{LANG_DEL}</a></font></div>
          </td>
        </tr>
        <tr> 
          <td bgcolor="#ffffff" width="69%"><font size="2">{SUBCAT_DESC} </font> 
            <p></p>
          </td>
        </tr>
<!-- END ForumBlock -->
<!-- END CatBlock --> 
        <tr> 
          <td colspan=2 bgcolor="#{THBG}">&nbsp; </td>
        </tr>
        </tbody> 
      </table>
    </td>
  </tr>
  </tbody>
</table>
