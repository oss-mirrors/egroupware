<!-- BEGIN Sieve Mail Filters -->

<FORM action="{form_edit_filter_action}" method="post">
		
	<h3><center>{lang_email_filters} - <em>Semi-Dumb UI</em></center></h3>
	
	<table width="90%" border="0" cellpadding="3" cellspacing="2" align="center">
	<tr bgcolor="{row_off}">
		<td colspan="4" align="left">
			<font size="-1">{lang_filter_number}:&nbsp;<strong>[{filternum}]</strong>
			&nbsp;&nbsp;
			{lang_filter_name}:&nbsp;<input size="30" name="{filter_name_box_name}" value="{filter_name_box_value}">
			</font>
		</td>
	</tr>
	
	<tr>
		<td colspan="4"><font size="-1">&nbsp;</font></td>
	</tr>
	
	<tr bgcolor="{row_on}">
		<td colspan="4">
			<strong>{lang_if_messages_match}</strong>
		</td>
	</tr>
	
	<!-- BEGIN B_matches_row -->
	<tr bgcolor="{row_off}">
		<td align="center">
			<font size="-1">{V_match_left_td}</font>
		</td>
		<td align="center">
			<font size="-1">
			<select name="{examine_selectbox_name}">
				<option value="from">{lang_from}</option>
				<option value="to">{lang_to}</option>
				<option value="cc">{lang_cc}</option>
				<option value="bcc">{lang_bcc}</option>
				<option value="recipient">{lang_recipient}</option>
				<option value="sender">{lang_sender}</option>
				<option value="subject">{lang_subject}</option>
				<option value="header">{lang_header}</option>
				<option value="size_larger">{lang_size_larger}</option>
				<option value="size_smaller">{lang_size_smaller}</option>
				<option value="allmessages">{lang_allmessages}</option>
				<option value="body">{lang_body}</option>
			</select>
			</font>
		</td>
		<td align="center">
			<font size="-1">
			<select name="{comparator_selectbox_name}">
				<option value="contains" selected>{lang_contains}</option>
				<option value="notcontains">{lang_notcontains}</option>
			</select>
			</font>
		</td>
		<td align="center">
			<font size="-1">
			<input size="20" name="{matchthis_textbox_name}" value="{match_textbox_txt}">
			</font>
		</td>
	</tr>
	<!-- END B_matches_row -->
	</table>

	<br>
	
	<table width="90%" border="0" cellpadding="3" cellspacing="2" align="center">
	<tr bgcolor="{row_on}">
		<td colspan="4">
			<strong>{lang_take_actions}</strong>
		</td>
	</tr>
	<!-- BEGIN B_actions_row -->
	<tr bgcolor="{row_off}">
		<td width="20%" align="center">
			<font size="-1">
			{V_action_widget}
			</font>
		</td>
		<td width="30%" align="center">
			<font size="-1">
			{folder_listbox}
			</font>
		</td>
		<td width="30%" align="center">
			<font size="-1">
			{lang_or_enter_text}&nbsp;
			<input size="20" name="{action_textbox_name}" value="{action_textbox_txt}">
			</font>
		</td>
		<td width="20%" align="center">
			<font size="-1">
			<input type="checkbox" name="{stop_filtering_checkbox_name}" value="True" {stop_filtering_checkbox_checked}>
			&nbsp;{lang_stop_if_matched}
			</font>
		</td>
	</tr>
	<!-- END B_actions_row -->
	</table>
	
	<br>
	
	<table width="75%" border="0" cellPadding="0" cellSpacing="0">
	<tr> 
		<td width="50%">
			&nbsp;
		</td>
		<td width="25%">
			<input type="submit" name="submit" value="{lang_submit}">
			&nbsp; &nbsp;
			<input type="reset" name="reset" value="{lang_clear}">
		</td>
	
</form>
	
<form action="{form_cancel_action}" method="post">
	
		<td width="25%">
			<input type="submit" name="cancel" value="{lang_cancel}">
		</td>
	
</form>
	
	</tr>
	</table>
	
	<p>&nbsp;</p>

	{V_mlist_html}
	

<!-- END Sieve Mail Filters -->
