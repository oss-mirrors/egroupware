<!-- BEGIN Sieve Mail Filters -->

	<br>
	<table width="90%" border="1" cellpadding="0" cellspacing="0" align="center">
	<tr> 
		<td width="100%" align="left">
			The data you submitted (if any) should be above this table:
		</td>
	</tr>
	<tr>
		<td width="100%" align="left">
			{data_dump_info}
		</td>
	</tr>
	<tr>
		<td width="100%" align="left">
			What part of the message you can examine:<br>
			<font size="-1">
			<ul>
				<li>{lang_from}: Matches the From address in the message header</li>
				<li>{lang_to}: Matches the To address in the message header</li>
				<li>{lang_cc}: Matches the CC address in the message header</li>
				<li>{lang_bcc}: Matches the Bcc address in the message header</li>
				<li>{lang_recipient}: Matches either the To, CC or Bcc addresses in the message header</li>
				<li>{lang_sender}: Matches the Sender address in the message header</li>
				<li>{lang_subject}: Matches the Subject text in the message header</li>
				<li>{lang_header}: Matches text in a chosen message header</li>
				<li>{lang_size_larger}: Matches the size of the message larger than X</li>
				<li>{lang_size_smaller}: Matches the size of the message smaller than X</li>
				<li>{lang_allmessages}: Always matches</li>
			</ul>
			</font>
		</td>
	</tr>
	<tr>
		<td width="100%" align="left">
			What works:<br>
			<font size="-1">
			<ul>
				<li>only the 1st row of "{lang_if_messages_match}" works</li>
				<li>when you submit the form, an IMAP search *might* be done</li>
				<li>Rule Matches are siaplayed as a message list, like a search result, up to about 20 hits</li>
				<li>to navigate athe whole result set, click button "submit to mlist class"</li>
				<li>besides viewing results, NO actions "{lang_take_actions}" are enabled yet</li>
			</ul>
			</font>
		</td>
	</tr>
	</table>

	<p>&nbsp;</p>

<FORM action="{form_edit_filter_action}" method="post">
		
	<h2><center>Sieve {filters_txt}<br>Semi-Dumb UI</center></h2>
	
	<table width="90%" border="0" align="center">
	<tr bgcolor="{row_off}">
		<td colspan="4" align="left">
			<font size="-1">
				&nbsp;{lang_name}:&nbsp;
			</font>
			<input size="30" name="{filter_name_box_name}" value="{filter_name}">
		</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td colspan="4">
			<font color="000000" face="">{lang_if_messages_match}:</font>
		</td>
	</tr>
	
	<!-- BEGIN B_matches_row -->
	<tr bgcolor="{row_off}">
		<td align="center">
			<font size="-1">
				{V_account_and_or_ignore}
			</font>
		</td>
		<td align="center">
			<font size="-1">
				<select name="filter_{f_idx}[match_{match_rownum}_examine]">
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
				<select name="filter_{f_idx}[match_{match_rownum}_comparator]">
					<option value="contains" selected>{lang_contains}</option>
					<option value="notcontains">{lang_notcontains}</option>
				</select>
			</font>
		</td>
		<td align="center">
			<font size="-1">
				<input size="20" name="filter_{f_idx}[match_{match_rownum}_matchthis]" value="{match_textbox_txt}">
			</font>
		</td>
	</tr>
	<!-- END B_matches_row -->
	</table>
	
	<p>&nbsp;</p>
	
	<table width="90%" border="0" align="center">
	<tr bgcolor="{row_on}">
		<td colspan="4">
			<font color="000000">{lang_take_actions}:</font>
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
				<input size="20" name="filter_{f_idx}[action_{action_rownum}_actiontext]" value="{action_textbox_txt}">
			</font>
		</td>
		<td width="20%" align="center">
			<font size="-1">
				<input type="checkbox" name="filter_{f_idx}[action_{action_rownum}_stop_filtering]" value="True">&nbsp;{lang_stop_if_matched}
			</font>
		</td>
	</tr>
	<!-- END B_actions_row -->
	</table>
	
	<p>&nbsp;</p>
	
	<table width="75%" border="0" cellPadding="0" cellSpacing="0">
	<tr> 
		<td width="50%">
			&nbsp;
		</td>
		<td width="25%">
			<input type="submit" name="{form_edit_filter_btn_name}" value="{lang_submit}">
			&nbsp; &nbsp;
			<input type="reset" name="reset" value="{lang_clear}">
		</td>
	
</form>
	
<form action="{form_cancel_action}" method="post">
	
		<td width="25%">
			<input type="submit" name="{form_cancel_btn_name}" value="{lang_cancel}">
		</td>
	
</form>
	
	</tr>
	</table>
	
	<p>&nbsp;</p>

	{V_mlist_html}
	

<!-- END Sieve Mail Filters -->
