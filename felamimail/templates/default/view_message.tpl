<!-- BEGIN message_navbar -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr bgcolor="{th_bg}">
		<td>
			{lang_back_to_folder}:&nbsp;<a class="head_link" href="{link_message_list}">{folder_name}</a>
			&nbsp;|&nbsp;
			<a class="head_link" href="{link_compose}">{lang_compose}</a>
		</td>
		<td align=right>
			<a href="{link_reply}">
			<img src="{app_image_path}/sm_reply.gif" height="26" width="28" alt="{lang_reply}" border="0">
			</a>
			
			<a href="{link_reply_all}">
			<img src="{app_image_path}/sm_reply_all.gif" height="26" width="28" alt="{lang_reply_all}" border="0">
			</a>
			
			<a href="{link_forward}">
			<img src="{app_image_path}/sm_forward.gif" height="26" width="28" alt="{lang_forward}" border="0">
			</a>
			
			<a href="{link_delete}">
			<img src="{app_image_path}/sm_delete.gif" height="26" width="28" alt="{lang_delete}" border="0">
			</a>
		</td>
		<td align="right">
			{left_arrow}
			{right_arrow}
		</td>
	</tr>
</table>
<!-- END message_navbar -->

<!-- BEGIN B_cc_data -->
<tr>
	<td valign="top" bgcolor={bg01}>
		<font size="2" face="{theme_font}">
		{lang_cc}:</font>
	</td> 
	<td colspan="2" bgcolor={row_on}>
		<font size="2" face="{theme_font}">
		{cc_data_final}
		</font>
	</td>
</tr>
<!-- END B_cc_data -->

<!-- BEGIN message_header -->
<table border="0" cellpadding="1" cellspacing="0" width="100%">
<tr>
	<td valign="top" width="10%" bgcolor={bg01}>
		{lang_from}:
	</td>
	<td bgcolor={row_on}>
		<font size="2" face="{theme_font}">
		<strong>{from_data}</strong>
		</font>
	</td>
	<td nowrap align=right width="1%" bgcolor={row_on}>
		<font size="2" face="{theme_font}">
		{view_header}
		</font>
	</td>
</tr>
<tr>
	<td valign="top" bgcolor="{bg01}">
		{lang_to}:
	</td> 
	<td colspan="2" bgcolor="{row_off}">
		<font size="2" face="{theme_font}">
		{to_data_final}
		</font>
	</td>
</tr>

{cc_data}

<tr>
	<td valign="top" bgcolor="{bg01}">
		<font size="2" face="{theme_font}">
		{lang_date}:</font>
	</td> 
	<td colspan="2" bgcolor="{bg_date}">
		<font size="2" face="{theme_font}">
		{date_data}
		</font>
	</td>
</tr>

<tr>
	<td valign="top" bgcolor="{bg01}">
		<font size="2" face="{theme_font}">
		{lang_subject}:</font>
	</td> 
	<td colspan="2" bgcolor="{bg_subject}">
		<font size="2" face="{theme_font}">
		<strong>{subject_data}</strong>
		</font>
	</td>
</tr>
</table>
<!-- END message_header -->
