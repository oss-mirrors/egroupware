<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
{hidden_vars}
{error}
<table border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td colspan="6" align="left">
			<table border="0" width="100%">
				<tr>
				{left}
					<td align="center">{lang_showing}</td>
				{right}
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="6" align="right">
			<form method="post" action="{searchurl}">
			<input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}">
			</form></td>
	</tr>
	<tr bgcolor="{th_bg}">
		<td width="10%" bgcolor="{th_bg}">{sort_num}</td>
		<td width="20%" bgcolor="{th_bg}">{sort_customer}</td>
		<td width="20%" bgcolor="{th_bg}">{sort_title}</td>
		<td width="10%" bgcolor="{th_bg}" align="center">{sort_date}</td>
		<td width="10%" align="right" bgcolor="{th_bg}">{currency}&nbsp;{sort_sum}</td>
		<td width="10%" bgcolor="{th_bg}" align="center">{h_lang_invoice}</td>
	</tr>
  
<!-- BEGIN projects_list -->
      
	<tr bgcolor="{tr_color}">
		<td>{num}</td>
		<td>{customer}</td>
		<td>{title}</td>
		<td align="center">{date}</td>
		<td align="right">{sum}</td>
		<td align="center"><a href="{invoice}">{lang_invoice}</a></td>
	</tr>

<!-- END projects_list -->

</table>
</center>
