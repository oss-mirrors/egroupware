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
<tr>
	<td bgcolor="{th_backcolor}">
		<font size="2" face="{the_font}"><strong>{label_name_text}</strong></font>
		&nbsp;&nbsp;&nbsp;<a href="{view_long_lnk}">({view_long_txt})</a>
		&nbsp;&nbsp;&nbsp;<a href="{view_short_lnk}">({view_short_txt})</a>
	</td>
	<td bgcolor="{th_backcolor}">
		<font size="2" face="{the_font}"><strong>{label_messages_text}</strong></font>
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
		<select name="source_folder">
			<option value="">{select_txt_rename}</option>
			{all_folders_listbox}
		</select>
		&nbsp;
		<select name="action">
			<option value="create">{form_create_txt}</option>
			<option value="delete">{form_delete_txt}</option>
			<option value="rename">{form_rename_txt}</option>
			<option value="create_expert">{form_create_expert_txt}</option>
			<option value="delete_expert">{form_delete_expert_txt}</option>
			<option value="rename_expert">{form_rename_expert_txt}</option>
		</select> 
		<input type="text" name="target_folder">
		<input type="submit" value="{form_submit_txt}">
	</td>
</tr>
</table>
</form>

<br>
<!-- end folder.tpl -->
