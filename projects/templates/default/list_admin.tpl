<!-- $Id$ -->
<center>
<table width="80%" border="0" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="3" align="center" bgcolor="{th_bg}"><b>{lang_action}</b></td>
	</tr>
	<tr>
		<td colspan="3" align="left">
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
		<td colspan="3" align="right">
			<form method="post" action="{actionurl}">
			<input type="text" name="query">&nbsp;<input type="submit" name="search" value="{lang_search}"></form></td>
	</tr>
	<tr bgcolor="{th_bg}">
		<td bgcolor="{th_bg}">{sort_lid}</td>
		<td bgcolor="{th_bg}">{sort_firstname}</td>
		<td bgcolor="{th_bg}">{sort_lastname}</td>
	</tr>

<!-- BEGIN admin_list -->

	<tr bgcolor="{tr_color}">                                                                                                                                                                
		<td>{lid}</td>
		<td>{lastname}</td>
		<td>{firstname}</td>
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
