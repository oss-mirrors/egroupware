<!-- begin message_main.tpl -->
<script type="text/javascript">
function do_action(act)
{
	document.delmov.what.value = act;
	document.delmov.submit();
}
</script>
<!-- BEGIN B_x-phpgw-type -->
<center>
<h1>THIS IS A phpGroupWare-{application} EMAIL</h1>
In the future, this will process a specially formated email msg.<hr>
</center>
<!-- END B_x-phpgw-type -->
<table border="0" cellpadding="2" cellspacing="1" width="95%" align="center">
<tr>
	<form name="{acctbox_frm_name}" action="{acctbox_action}" method="post">
	<td bgcolor="{ctrl_bar_back1}" width="50%" align="left">
			&nbsp;&nbsp;&nbsp;{acctbox_listbox}
			<font face="{ctrl_bar_font}" size="{ctrl_bar_font_size}">&nbsp;&nbsp;{accounts_label}</font>
	</td>
	</form>
	<form name="{frm_delmov_name}" action="{frm_delmov_action}" method="post">
	<input type="hidden" name="what" value="delete">
	<input type="hidden" name="sort" value="{current_sort}">
	<input type="hidden" name="order" value="{current_order}">
	<input type="hidden" name="start" value="{current_start}">
	<input type="hidden" name="{mlist_checkbox_name}" value="{mlist_embedded_uri}">
	<td bgcolor="{ctrl_bar_back1}" width="50%" align="right">
		<font face="{ctrl_bar_font}" size="{ctrl_bar_font_size}">{delmov_listbox}&nbsp;</font>
	</td>
	</form>
</tr>
</table>

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
				{ilnk_reply}&nbsp;{ilnk_replyall}&nbsp;{ilnk_forward}&nbsp;{ilnk_delete}&nbsp;
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
		{from_data_final}
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

<tr>
	<td valign="top" colspan="2" width="100%">
		<table border="0" cellpadding="1" cellspacing="1" width="100%" align="center">
		<tr>
			<td bgcolor="{theme_row_on}" align="center" width="33%">
				<font size="2" face="{theme_font}">{view_option}</font>
			</td>
			<td bgcolor="{theme_row_on}" align="center" width="34%">
				<font size="2" face="{theme_font}">{view_headers_href}</font>
			</td>
			<td bgcolor="{theme_row_on}" align="center" width="33%">
				<font size="2" face="{theme_font}">{view_raw_message_href}</font>
			</td>
		</tr>
		</table>	
	</td> 
</tr>
</table>

<!-- start message display -->
<br>
<table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<!-- BEGIN B_debug_parts -->
<tr>
	<td align="left">
		{msg_body_info}
	</td>
</tr>
<!-- END B_debug_parts -->

<!-- BEGIN B_display_part -->
<tr>
	<td bgcolor="{theme_row_on}" width="100%">
		<font size="2" face="{theme_font}">
		<strong>{title_text}</strong> &nbsp; &nbsp; {display_str}</font>
	</td>
</tr>
<tr>
	<td align="left">
		<br>{message_body}
	</td>
</tr>
<!-- END B_display_part -->

</table>

<!-- end message_main.tpl -->
