<!-- $Id$ -->
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>                                                                                                      
<hr noshade width="98%" align="center" size="1">
<center>
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
		<td width="8%" bgcolor="{th_bg}">{sort_num}</td>
		<td width="30%" bgcolor="{th_bg}">{sort_descr}</td>
		<td width="10%" bgcolor="{th_bg}" align="right">{currency}&nbsp;{sort_billperae}</td>
		<td width="10%" bgcolor="{th_bg}" align="right">{sort_minperae}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{lang_edit}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{lang_delete}</td>
	</tr>

<!-- BEGIN activities_list -->

	<tr bgcolor="{tr_color}">
		<td>{num}</td>
		<td>{descr}</td>
		<td align="right">{billperae}</td>
		<td align="right">{minperae}</td>
		<td align="center"><a href="{edit}">{lang_edit}</a></td>
		<td align="center"><a href="{delete}">{lang_delete}</a></td>
	</tr>

<!-- END activities_list -->

</table>
<table border="0" cellpadding="2" cellspacing="2">
	<tr>
		<td><form method="POST" action="{actionurl}">
			{hidden_vars}
			<input type="submit" name="Add" value="{lang_add}"></form></td>
		<td><form method="POST" action="{projectsurl}"> 
			<input type="submit" name="Add" value="{lang_projects}"></form></td>
	</tr>
</table>
</center>
