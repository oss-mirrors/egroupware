<!-- $Id$ -->

{app_header}

<center>
<table border="0" width="98%" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="6">
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
		<td colspan="2" width="25%" align="left">
			{action_list}
			<noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript></form></td>
		<td width="20%" align="center"><form method="POST" name="status" action="{status_action}">
			<select name="status" onChange="this.form.submit();">{status_list}</select><noscript>
			&nbsp;<input type="submit" name="submit" value="Submit"></noscript></form></td>
		<td width="20%" align="center"><form method="POST" name="filter" action="{filter_action}">{filter_list}</form></td>
		<td colspan="2" width="35%" align="right"><form method="POST" name="query" action="{search_action}">{search_list}</form></td>
	</tr>
</table>
<table border="0" width="98%" cellpadding="2" cellspacing="2">
	<tr bgcolor="{th_bg}">
		<td width="20%">{sort_number}</td>
		<td width="20%">{sort_investment_nr}</td>
		<td width="30%">{sort_title}</td>
		<td width="15%" align="right">{currency}&nbsp;{sort_pcosts}</td>
        <td width="10%" align="right">{currency}&nbsp;{sort_budget}</td>
		<td width="5%" align="center">{lang_view}</td>
	</tr>

<!-- BEGIN projects_list -->

	<tr bgcolor="{tr_color}">
		<td>{number}</td>
		<td>{investment_nr}</td>
		<td>{title}</td>
		<td align="right">{pcosts}</td>
        <td align="right">{budget}</td>
		<td align="center"><a href="{view}">{lang_view_entry}</a></td>
	</tr>

<!-- END projects_list -->

	<tr height="15">
		<td>&nbsp;</td>
	<tr>
	<tr bgcolor="{th_bg}">
		<td><b>{lang_sum_pcosts}:&nbsp;{currency}</b></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td align="right"><b>{sum_pcosts}</b></td>
        <td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>

	<tr bgcolor="{th_bg}">
		<td><b>{lang_sum_budget}:&nbsp;{currency}</b></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
        <td align="right"><b>{sum_budget}</b></td>
		<td>&nbsp;</td>
	</tr>

</table>
</center>
