<!-- $Id$ -->

{app_header}

<p><b>&nbsp;&nbsp;&nbsp;{lang_header}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<table border="0" width="98%" cellpadding="2" cellspacing="2">
	<tr colspan="9">
		<td colspan="9">
			<table border="0" width="100%">
				<tr>
				{left}
					<td align="center">{lang_showing}</td>
				{right}
				</tr>
			</table>
		</td>
	</tr>
	<tr colspan="9">
		<td width="33%" align="left">
			{action_list}
			<noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript></form></td>
		<td width="33%" align="center"><form method="POST" name="filter" action="{filter_action}">{filter_list}</form></td>
		<td width="33%" align="right"><form method="POST" name="query" action="{search_action}">{search_list}</form></td>
	</tr>
</table>
<table border="0" width="98%" cellpadding="2" cellspacing="2">
	<tr bgcolor="{th_bg}">
		<td width="8%" bgcolor="{th_bg}">{sort_number}</td>
		<td width="20%" bgcolor="{th_bg}">{sort_title}</td>
		<td width="20%" bgcolor="{th_bg}">{sort_coordinator}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{sort_status}</td>
        <td width="20%" bgcolor="{th_bg}">{sort_action}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{sort_end_date}</td>
		{lang_action}
		<td align="center" width="5%" bgcolor="{th_bg}">{lang_stat}</td>
	</tr>
  
<!-- BEGIN projects_list -->

	<tr bgcolor="{tr_color}">
		<td>{number}</td>
		<td>{title}</td>
		<td>{coordinator}</td>
		<td align="center">{status}</td>
        <td>{td_action}</td>
		<td align="center">{end_date}</td>
		{action_entry}
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
