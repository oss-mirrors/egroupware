<!-- begin message_main.tpl -->
<table cellpadding="1" cellspacing="1" width="95%" align="center">
<tr>
	<td colspan="2" bgcolor="{reply_btns_bkcolor}">
		<table border="0" cellpadding="0" cellspacing="1" width="100%">
		<tr>
			<td>
				<font size="3" face="{theme_font}" color="{reply_btns_text}">
				{lnk_goback_folder}
				</font>
			</td>
			<td align="right">
				<font size="3" face="{theme_font}" color="{reply_btns_text}">
				{ilnk_reply}
				{ilnk_replyall}
				{ilnk_forward}
				{ilnk_delete}
 				</font>
			</td>
			<td align="right">
				{ilnk_prev_msg}
				{ilnk_next_msg}
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td bgcolor="{tofrom_labels_bkcolor}" valign="top">
		<font size="2" face="{theme_font}">
		<strong>{lang_from}:</strong></font>
	</td>
	<td bgcolor="{tofrom_data_bkcolor}" width="570">
		<font size="2" face="{theme_font}">
		{from_real_name}
		{from_raw_addy}
		{from_addybook_add}
		</font>
	</td>
</tr>
<tr>
	<td bgcolor="{tofrom_labels_bkcolor}" valign="top">
		<font size="2" face="{theme_font}">
		<strong>{lang_to}:</strong></font>
	</td> 
	<td bgcolor="{tofrom_data_bkcolor}" width="570">
		<font size="2" face="{theme_font}">
		{to_data_final}
		</font>
	</td>
</tr>

<!-- BEGIN B_cc_data -->
<tr>
	<td bgcolor="{tofrom_labels_bkcolor}" valign="top">
		<font size="2" face="{theme_font}">
		<strong>{lang_cc}:</strong></font>
	</td> 
	<td bgcolor="{tofrom_data_bkcolor}" width="570">
		<font size="2" face="{theme_font}">
		{cc_data_final}
		</font>
	</td>
</tr>
<!-- END B_cc_data -->

<tr>
	<td bgcolor="{tofrom_labels_bkcolor}" valign="top">
		<font size="2" face="{theme_font}">
		<strong>{lang_date}:</strong></font>
	</td> 
	<td bgcolor="{tofrom_data_bkcolor}" width="570">
		<font size="2" face="{theme_font}">
		{message_date}
		</font>
	</td>
</tr>

<!-- BEGIN B_attach_list -->
<tr>
	<td bgcolor="{tofrom_labels_bkcolor}" valign="top">
		<font size="2" face="{theme_font}">
		<strong>{lang_files}:</strong></font>
	</td> 
	<td bgcolor="{tofrom_data_bkcolor}" width="570">
		<font size="2" face="{theme_font}">
		{list_of_files}
		</font>
	</td>
</tr>
<!-- END B_attach_list -->

<tr>
	<td bgcolor="{tofrom_labels_bkcolor}" valign="top">
		<font size="2" face="{theme_font}">
		<strong>{lang_subject}:</strong></font>
	</td> 
	<td bgcolor="{tofrom_data_bkcolor}" width="570">
		<font size="2" face="{theme_font}">
		{message_subject}
		</font>
	</td>
</tr>
</table>
<!-- here starts actual message content display -->
<br>
<table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<tr>
	<td align="center">

<!-- end message_main.tpl -->