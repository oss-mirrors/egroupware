<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                     
<hr noshade width="98%" align="center" size="1">
<center>
 {common_hidden_vars}
 {total_matchs}
 {next_matchs}
 {error}	
  <table width=85% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_num}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_customer}</td>
      <td width="20%" bgcolor="{th_bg}" align=center>{sort_title}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_date}</td>
      <td width="10%" align=right bgcolor="{th_bg}">{currency}&nbsp;{sort_sum}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{h_lang_invoice}</td>
    </tr>
  </form>
  
<!-- BEGIN projects_list -->
      <tr bgcolor="{tr_color}">
        <td>{num}</td>
        <td>{customer}</td>
        <td>{title}</td>
        <td align=center>{date}</td>
        <td align=right>{sum}</td>
        <td align=center>{invoice}</td>
      </tr>
<!-- END projects_list -->

  </table>
</center>
