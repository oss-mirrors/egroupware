<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
{message}
<form method="POST" action="{actionurl}">
<table border=0 cellspacing=1 cellpadding=3>
<tr>
<td>{lang_choose}</td>
<td>{choose}</td>
</tr>
<tr>
<td>{title_delivery_num} :</td>
<td><input type=text name="delivery_num" value="{delivery_num}"></td>
</tr>
<tr>
<td>{title_customer} :</td>
<td>{customer}</td>
</tr>
<tr>
<td>{title_project} :</td>
<td>{project}</td>
</tr>
<tr>
<td>{lang_delivery_date} :</td>
<td>{date_select}</td>
</tr>
</table> 

 {hidden_vars}
  
   <table width=100% border=0 cellspacing=1 cellpadding=3>
    <tr bgcolor="{th_bg}">
      <td width="3%" bgcolor="{th_bg}" align=center>{h_lang_select}</td>
      <td width="25%" bgcolor="{th_bg}" align=center>{sort_activity}</td>
      <td width="25%" bgcolor="{th_bg}" align=center>{sort_hours_descr}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_status}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_start_date}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{sort_aes}</td>
      <td width="10%" bgcolor="{th_bg}" align=center>{h_lang_edithour}</td>
    </tr>

<!-- BEGIN projecthours_list -->

      <tr bgcolor="{tr_color}">
        <td align=center>{select}</td>
        <td>{activity}</td>
        <td>{hours_descr}</td>
        <td align=center>{status}</td>
        <td align=center>{start_date}</td>
        <td align=right>{aes}</td>
        <td align=center><a href="{edithour}">{lang_edit_entry}</a></td>
      </tr>

<!-- END projecthours_list -->
</table><br><br>

<table width=100% border=0 cellspacing=0 cellpadding=0>
      <tr bgcolor="{tr_color}">
        <td width="3%">&nbsp;</td>
        <td width="25%">&nbsp;</td>
        <td width="25%" align="center"><font size="4"><b>{lang_aes}</b></font></td>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%" align="right"><font size="4"><b>{sum_aes}</b></font></td>
        <td width="10%">&nbsp;</td>
      </tr>
</table>
<table width="50%" border=0 cellpadding="2" cellspacing="2">
<tr>
<td align="center">{delivery}</td>
  </form>
<!-- url zum druck -->
<td align="center"><a href={print_delivery} target=_blank>{lang_print_delivery}</a></td>
</tr>
</table>
</center>