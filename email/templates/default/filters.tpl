<!-- BEGIN Sieve Mail Filters -->

	<table width="75%" border="1" cellpadding="0" cellspacing="0" align="center">
	<tr> 
		<td width="100%" align="left">
			Here is the data you submitted (if any):
		</td>
	</tr>
	<tr>
		<td width="100%" align="left">
			{data_dump}
		</td>
	</tr>
	</table>

	<p>&nbsp;</p>

<FORM action="{form1_action}" method="post">
		
	<h2><center>Sieve {filters_txt}<br>Dummy UI</center></h2>
	
	<table width="75%" border="0" align="center">
	<tr bgcolor="{row_off}">
		<td colspan="4" align="left">
			<font size="-1">
				&nbsp;{lang_name}:&nbsp;
			</font>
			<input size="30" name="filter_{f_idx}[filtername]" value="{filter_name}">
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
				<select name="filter_{f_idx}[match_{match_rownum}_andor]">
					<option value="or" selected>{lang_or}</option>
					<option value="and">{lang_and}</option>
				</select>
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
					<option value="sender"selected>{lang_sender}</option>
					<option value="subject">{lang_subject}</option>
					<option value="header">{lang_header}</option>
					<option value="size">{lang_size}</option>
					<option value="allmessages">{lang_allmessages}</option>
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
	
	<tr bgcolor="{row_on}">
		<td align="center">
			<font size="-1">
				<b>[{lang_more_choices}]</b>
			</font>
		</td>
		<td align="center">
			<font size="-1">
				<b>[{lang_fewer_choices}]</b>
			</font>
		</td>
		<td align="center">
			<font size="-1">
				&nbsp;
			</font>
		</td>
		<td align="center">
			<font size="-1">
				<b>[{lang_reset}]</b>
			</font>
		</td>
	</tr>
	</table>
	
	<p>&nbsp;</p>
	
	<table width="75%" border="0" align="center">
	<tr bgcolor="{row_on}">
		<td colspan="4">
			<font color="000000">{lang_take_actions}:</font>
		</td>
	</tr>
	<!-- BEGIN B_actions_row -->
	<tr bgcolor="{row_off}">
		<td width="20%" align="center">
			<font size="-1">
				<select name="filter_{f_idx}[action_{action_rownum}_judgement]">
					<option value="keep">{lang_keep}</option>
					<option value="discard">{lang_discard}</option>
					<option value="reject">{lang_reject}</option>
					<option value="redirect">{lang_redirect}</option>
					<option value="fileinto" selected>{lang_fileinto}</option>
				</select>
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
				<input type="checkbox" name="filter_{f_idx}[action_{action_rownum}_stop]">&nbsp;{lang_stop_if_matched}
			</font>
		</td>
	</tr>
	<!-- END B_actions_row -->
	<tr bgcolor="{row_on}">
		<td align="center">
			<font size="-1">
				<b>[{lang_more_actions}]</b>
			</font>
		</td>
		<td align="center">
			<font size="-1">
				<b>[{lang_fewer_actions}]</b>
			</font>
		</td>
		<td align="center">
			<font size="-1">
				&nbsp;
			</font>
		</td>
		<td colspan="2" align="center">
			<font size="-1">
				<b>[{lang_reset}]</b>
			</font>
		</td>
	</tr>
	</table>
	
	<p>&nbsp;</p>
	
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
	
<form action="{form2_action}" method="post">
	
		<td width="25%">
			<input type="submit" name="cancel" value="{lang_cancel}">
		</td>
	
</form>
	
	</tr>
	</table>
	
	<td>&nbsp;</td>

<!-- END Sieve Mail Filters -->
