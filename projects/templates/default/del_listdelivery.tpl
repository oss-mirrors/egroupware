<!-- $Id$ -->

{app_header}

<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<table width="79%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td colspan="4">
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
		<td width="33%">&nbsp;</td>
		<td width="33%">&nbsp;</td>
		<td width="33%" align="right">
			<form method="POST" name="query" action="{search_action}">{search_list}</form></td>
	</tr>
</table>
<table width="79%" border="0" cellspacing="2" cellpadding="2">
	<tr bgcolor="{th_bg}">
		<td width="10%" bgcolor="{th_bg}">{sort_num}</td>
		<td width="10%" bgcolor="{th_bg}">{sort_customer}</td>
		<td width="10%" bgcolor="{th_bg}">{sort_title}</td>
		<td width="10%" bgcolor="{th_bg}" align="center">{sort_date}</td>
		<td width="10%" bgcolor="{th_bg}" align="center">{h_lang_delivery}</td>
	</tr>
  
<!-- BEGIN projects_list -->

	<tr bgcolor="{tr_color}">
		<td>{num}</td>
		<td>{customer}</td>
		<td>{title}</td>
		<td align="center">{date}</td>
		<td align="center"><a href="{delivery}">{lang_delivery}</a></td>
	</tr>

<!-- END projects_list -->

</table>
</center>
