<!-- $Id$ -->

{app_header}

<center>
<table border="0" width="98%" cellpadding="2" cellspacing="2">
	<tr>
		<td width="100%" colspan="4">
			<table border="0" width="100%">
				<tr>
				{left}
					<td align="center" width="100%">{lang_showing}</td>
				{right}
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="30%" align="left">
			{action_list}
			<noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript></form>
		</td>
		<td width="20%" align="center">
			<form method="POST" name="status" action="{action_url}">
				<select name="status" onChange="this.form.submit();">{status_list}</select>
				<noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript>
			</form>
		</td>
		<td width="25%" align="center"><form method="POST" name="filter" action="{action_url}">{filter_list}</form></td>
		<td width="25%" align="right"><form method="POST" name="query" action="{action_url}">{search_list}</form></td>
	</tr>
</table>
<table border="0" width="98%" cellpadding="2" cellspacing="2">
	<tr bgcolor="{th_bg}">
		<td width="8%">{sort_number}</td>
		<td width="20%">{sort_title}</td>
		<td width="20%">{sort_coordinator}</td>
        <td width="20%">{sort_action}</td>
		<td width="8%" align="center">{sort_sdate}</td>
		<td width="8%" align="center">{sort_edate}</td>
		<td align="center" width="5%">{lang_stat}</td>
	</tr>

<!-- BEGIN projects_list -->

	<tr bgcolor="{tr_color}">
		<td>{number}</td>
		<td>{title}</td>
		<td>{coordinator}</td>
        <td>{td_action}</td>
		<td align="center">{sdate}</td>
		<td align="center">{edate}</td>
		<td align="center"><a href="{stat}">{lang_stat_entry}</a></td>
	</tr>

<!-- END projects_list -->
   
</table>
<table cellpadding="2" cellspacing="2">                                                                                                                                           
	<tr>
		<form method="POST" action="{userstats_action}">                                                                                                                                 
		<td><input type="submit" name="submit" value="{lang_userstats}"></form></td>                                                                                                
	</tr>                                                                                                                                                                       
</table>
</center>
