<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
{hidden_vars}
<table border="0" width="100%">
	<tr>
		<td width="33%" align="left">
			<form action="{cat_url}" name="form" method="POST">
			{lang_category}&nbsp;&nbsp;<select name="cat_id" onChange="this.form.submit();"><option value="">{lang_all}</option>{category_list}</select>
			<noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript></form></td>
		<td width="33%" align="center">{lang_showing}</td>
		<td width="33%" align="right">
			<form method="POST" action="{search_url}">
			<input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}">
			</form></td>
	</tr>
	<tr>
		<td colspan="8">
			<table border="0" width="100%">
				<tr>
				{left}
					<td>&nbsp;</td>
				{right}
				</tr>
			</table>
		</td>
	</tr>
</table>
<table border="0" width="100%" cellpadding="2" cellspacing="2">
	<tr bgcolor="{th_bg}">
		<td width="8%" bgcolor="{th_bg}">{sort_num}</td>
		<td width="20%">{sort_customer}</td>
		<td width="20%">{sort_title}</td>
		<td width="20%" bgcolor="{th_bg}">{sort_coordinator}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{sort_status}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{sort_end_date}</td>
		<td width="8%" align="center">{h_lang_part}</td>
		<td width="8%" align="center">{h_lang_partlist}</td>
	</tr>
  
<!-- BEGIN projects_list -->

	<tr bgcolor="{tr_color}">
		<td>{number}</td>
		<td>{customer}</td>
		<td>{title}</td>
		<td>{coordinator}</td>
		<td align="center">{status}</td>
		<td align="center">{end_date}</td>
		<td align="center"><a href="{part}">{lang_part}</a></td>
		<td align="center"><a href="{partlist}">{lang_partlist}</a></td>
	</tr>

<!-- END projects_list -->

</table><br><br>

<!-- link fuer alle invoices -->

<table border="0" cellpadding="2" cellspacing="2">     
	<tr>
		<td><a href="{all_partlist}">{lang_all_partlist}</a></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><a href="{all_part2list}">{lang_all_part2list}</a></td>
	</tr>
</table>
</center>
