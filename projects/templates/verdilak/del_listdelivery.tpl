<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                                                                     
<hr noshade width="98%" align="center" size="1">                                                                                                                                                  
<center>
 {total_matchs}
 {next_matchs}
	
  <table width=85% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_num}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_customer}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_title}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_date}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{h_lang_delivery}</td>
    </tr>
  </form>
  
<!-- BEGIN delivery_list -->

      <tr bgcolor="{tr_color}">
        <td>{num}</td>
        <td>{customer}</td>
        <td>{title}</td>
        <td align=center>{date}</td>
        <td align=center>{delivery}</td>
      </tr>

<!-- END delivery_list -->

  </table>
</center>
