<!-- BEGIN message_main -->
<table border="0" width="100%" cellspacing="0" bgcolor="white">
<tr>
	<td>
		{navbar}
	</td>
</tr>
<tr>
	<td>
{header}
	</td>
</tr>
{rawheader}
<tr>
	<td bgcolor="white">
<pre><font face="Verdana" size="-1">
{body}
</font></pre>
	<td>
</tr>
<tr>
	<td>
		<br>
		<table border="0" cellspacing="1" width="100%" bgcolor="white">
			{attachment_rows}
		</table>
	</td>
</tr>
</table>
<!-- END message_main -->

<!-- BEGIN message_raw_header -->
<tr>
	<td bgcolor="white">
		<pre><font face="Verdana" size="-1">{raw_header_data}</font></pre>
	</td>
</tr>
<!-- END message_raw_header -->

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
			<!-- {left_arrow}
			{right_arrow}-->
		</td>
	</tr>
</table>
<!-- END message_navbar -->

<!-- BEGIN message_navbar_print -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr bgcolor="{th_bg}">
		<td align="center">
			<a href="javascript:window.print()">{lang_print_this_page}</a>
		</td>
		<td align="center">
			<a href="javascript:window.close()">{lang_close_this_page}</a>
		</td>
	</tr>
</table>
<!-- END message_navbar_print -->

<!-- BEGIN message_attachement_row -->
<tr>
	<td valign="top" bgcolor={bg01}>
		<a href="{link_view}"><font size="2" face="{theme_font}">
		<b>{filename}</b></font><a>
	</td> 
	<td colspan="2" bgcolor={bg01}>
		<font size="2" face="{theme_font}">
		{mimetype}
		</font>
	</td>
	<td colspan="2" bgcolor={bg01}>
		<font size="2" face="{theme_font}">
		{size}
		</font>
	</td>
	<td colspan="2" bgcolor={bg01} width="10%" align="center">
		<font size="2" face="{theme_font}">
		<a href="{link_save}">{lang_save}</a>
		</font>
	</td>
</tr>
<!-- END message_attachement_row -->

<!-- BEGIN message_cc -->
<tr>
	<td valign="top" bgcolor={bg01}>
		<font size="2" face="{theme_font}">
		{lang_cc}:</font>
	</td> 
	<td colspan="2" bgcolor={bg01}>
		<font size="2" face="{theme_font}">
		{cc_data}
		</font>
	</td>
</tr>
<!-- END message_cc -->

<!-- BEGIN message_header -->
<table border="0" cellpadding="1" cellspacing="0" width="100%">
<tr>
	<td valign="top" width="10%" bgcolor={bg01}>
		{lang_from}:
	</td>
	<td bgcolor={bg01}>
		<font size="2" face="{theme_font}">
		<strong>{from_data}</strong>
		</font>
	</td>
	<td nowrap align=right width="1%" bgcolor={bg01}>
		<font size="2" face="{theme_font}">
		<a href="{link_header}">{view_header}</a>
		</font>
	</td>
</tr>
<tr>
	<td valign="top" bgcolor="{bg01}">
		{lang_to}:
	</td> 
	<td colspan="1" bgcolor="{bg01}">
		<font size="2" face="{theme_font}">
		{to_data}
		</font>
	</td>
	<td nowrap align=right width="1%" bgcolor={bg01}>
		<font size="2" face="{theme_font}">
		<a href="{link_printable}" target="_blank">{lang_printable}</a>
		</font>
	</td>
</tr>

{cc_data_part}

<tr>
	<td valign="top" bgcolor="{bg01}">
		<font size="2" face="{theme_font}">
		{lang_date}:</font>
	</td> 
	<td colspan="2" bgcolor="{bg01}">
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
	<td colspan="2" bgcolor="{bg01}">
		<font size="2" face="{theme_font}">
		<strong>{subject_data}</strong>
		</font>
	</td>
</tr>
</table>
<!-- END message_header -->
