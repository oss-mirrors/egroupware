<!-- $Id$ -->
<center>
<table width="80%" border="0" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="4" align="center" bgcolor="{th_bg}"><b>{lang_action}&nbsp;{lang_admin}</b></td>
	</tr>
	<tr>
		<td colspan="4" align="left">
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
		<td width="33%" align="right"><form method="POST" name="query" action="{search_action}">{search_list}</form></td>
	</tr>
</table>
<table width="80%" border="0" cellpadding="2" cellspacing="2">	
	<tr bgcolor="{th_bg}">
		<td width="33%" bgcolor="{th_bg}">{sort_lid}</td>
		<td width="33%" bgcolor="{th_bg}">{sort_firstname}</td>
		<td width="33%" bgcolor="{th_bg}">{sort_lastname}</td>
	</tr>

<!-- BEGIN admin_list -->

	<tr bgcolor="{tr_color}">                                                                                                                                                                
		<td>{lid}</td>
		<td>{firstname}</td>
		<td>{lastname}</td>
	</tr>

<!-- END admin_list -->

	<tr valign="bottom">
		<td>
			<form method="POST" action="{addurl}">
			<input type="submit" name="add" value="{lang_edit}"></form>
		</td>
	</tr>
	<tr valign="bottom">
		<td>
			<form method="POST" action="{doneurl}">
			<input type="submit" name="done" value="{lang_done}"></form>
		</td>
	</tr>
</table>
</center>
