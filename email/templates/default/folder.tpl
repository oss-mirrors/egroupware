<!-- begin folder.tpl -->
<form action="{form_action}" method="post">

<!-- BEGIN B_action_report -->
<center><p>{action_report}</p></center>
<!-- END B_action_report -->

<table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<tr>
	<td colspan="2" bgcolor="{title_backcolor}">
		&nbsp;<font size="3" face="{the_font}" color="{title_textcolor}"><strong>{title_text}<strong></font>

	</td>
</tr>

<!-- <tr><td>d_server_str: {debug_server_str} d_namespace: {debug_namespace} d_delimiter: {debug_delimiter} d_folder: {debug_folder} d_folder_long: {debug_folder_long} d_folder_short: {debug_folder_short}</td></tr> -->

<tr>
	<td bgcolor="{th_backcolor}">
		<font size="2" face="{the_font}"><strong>Folder name</strong></font>
	</td>
	<td bgcolor="{th_backcolor}">
		<font size="2" face="{the_font}"><strong>Messages</strong></font>
	</td>
</tr>

<!-- BEGIN B_folder_list -->
<tr>
	<td bgcolor="{list_backcolor}">
		<font size="2" face="{the_font}">
		<a href="{folder_link}">{folder_name}</a>
		</font>
	</td>
	<td bgcolor="{list_backcolor}" width="20%">
		<font size="2" face="{the_font}">
			{msgs_unseen}/{msgs_total}
		</font>
	</td>
</tr>
<!-- END B_folder_list -->

<tr>
	<td colspan="2" align="right" bgcolor="{th_backcolor}">
		<select name="action">
			<option value="create">{form_create_txt}</option>
			<option value="delete">{form_delete_txt}</option>
		</select> 
		<input type="text" name="target_folder">
		<input type="submit" value="{form_submit_txt}">
	</td>
</tr>
</table>
</form>

<br>
<!-- end folder.tpl -->