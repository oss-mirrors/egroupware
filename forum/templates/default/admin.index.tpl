
<p>&nbsp;</p>
<table align=center bgcolor=#{TB_BG} border=0 cellpadding=0 cellspacing=0 width="80%">
  <tbody> 
  <tr> 
    <td> 
      <table align=center border=0 cellpadding=0 cellspacing=1 width="100%">
        <tr> 
          <td colspan="3" bgcolor="{TR_BG}"> 
            <table border="0" width=100% align="center" cellspacing="1" cellpadding="0">
              <tr> 
                <td bgcolor="{BG6}" align="left">{FORUM_ADMIN}</td>
              </tr>
              <tr> 
                <td bgcolor="{TD_BG}" align="left">[<font size=-1><a href="{CAT_LINK}">{LANG_CAT} 
                  </a> | <a href="{FOR_LINK}">{LANG_FOR}</a> | <a href="{MAIN_LINK}">{LANG_MAIN}</a></font>]</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr bgcolor="#{TB_BG}"> 
          <td colspan="3"> 
            <div align="center">{LANG_CURRENT_SUBFORUM} </div>
          </td>
        </tr>
        <!-- BEGIN CatBlock --> 
          <tr bgcolor="#999999"> 
          <td colspan="3" height="20">&nbsp;</td>
        </tr>
		<tr> 
          <td bgcolor="#{TD_BG}" width="4%"><img src="{IMG_URL_PREFIX}category.gif" width="18" height="18" alt="{LANG_CATEGORY}"></td>
          <td bgcolor="#{TD_BG}" width="69%">{CAT_NAME}</td>
          <td rowspan="2" bgcolor="#{TD_BG}" width="27%"> 
            <div align="center">[ <a href="{EDIT_LINK}"><font size="2">{LANG_EDIT}</font></a><font size="2"> |  
              <a href="{DEL_LINK}">{LANG_DEL}</a> ]</font></div>
          </td>
        </tr>
        <tr> 
          <td bgcolor="#{TD_BG}" width="4%">&nbsp;</td>
          <td bgcolor="#{TD_BG}" width="69%"><font size="2">{CAT_DESC}</font></td>
        </tr>

        <!-- BEGIN ForumBlock --> 
        <tr bgcolor="{TR_BG}"> 
          <td bgcolor="#{TR_BG}" width="4%"><img src="{IMG_URL_PREFIX}forum.gif" width="16" height="16" alt="{LANG_SUBCAT}"></td>
          <td bgcolor="#{TD_BG}" width="69%">{SUBCAT_NAME} </td>
          <td bgcolor="#{TD_BG}" width="27%" rowspan="2"> 
            <div align="center"><font size=2>[ <a href="{SUBEDIT_LINK}">{LANG_EDIT}</a> 
              | <a href="{SUBDEL_LINK}">{LANG_DEL}</a> ]</font></div>
          </td>
        </tr>
        <tr> 
          <td bgcolor="#{TD_BG}" width="4%">&nbsp;</td>
          <td bgcolor="#{TD_BG}" width="69%"><font size="2">{SUBCAT_DESC} </font> 
            <p></p>
          </td>
        </tr>
        <!-- END ForumBlock --> 
<!-- END CatBlock --> 
          <tr bgcolor="#999999"> 
          <td colspan="3" height="20">&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
  </tbody>
</table>
