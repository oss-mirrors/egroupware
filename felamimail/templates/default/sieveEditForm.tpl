<!-- BEGIN main -->
<script language="JavaScript" type="text/javascript">

function SubmitForm(a)
{
    if (a == 'delete'){
	if (!confirm("Are you sure you want to delete this rule?")){
                return true;
        }
    }
    document.thisRule.submit();
}

</script>
<form ACTION="{action_url}" METHOD="post" NAME="thisRule">

<table WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
	<tr>
		<td CLASS="main">
			<table WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
				<tr CLASS="heading">
					<td>
						New Mail Filter Rule</td><td>&nbsp;      
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td CLASS="main">
			<table WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
				<tr>
					<td>
						<input TYPE="checkbox" NAME="continue" VALUE="continue" {continue_checked}>Check message against next rule also
						<input TYPE="checkbox" NAME="keep" VALUE="keep" {keep_checked}>Keep a copy of the message in your Inbox
						<input TYPE="checkbox" NAME="regexp" VALUE="regexp" {regexp_checked}>Use regular expressions
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td CLASS="main">
			<table WIDTH="100%" CELLPADDING="5" BORDER="1" CELLSPACING="0">
				<tr>
					<td CLASS="heading">
						CONDITIONS:
					</td>
					<td CLASS="heading">
						&nbsp;
					</td>
				</tr>
				<tr>
					<td NOWRAP="nowrap" rowspan="5">
						Match
						<select NAME="anyof">
							<option VALUE="0" {anyof_selected0}> all of
							<option VALUE="1" {anyof_selected4}> any of
						</select>
					</td>
					<td NOWRAP="nowrap">
						If message 'From:' contains: <input TYPE="text" NAME="from" SIZE="50" value="{value_from}">
					</td>
				</tr>
				<tr>
					<td>
						If message 'To:' contains: <input TYPE="text" NAME="to" SIZE="50" value="{value_to}">
					</td>
				</tr>
				<tr>
					<td>
						If message 'Subject:' contains: <input TYPE="text" NAME="subject" SIZE="50" value="{value_subject}">
					</td>
				</tr>
				<tr>
					<td>
						If message size is
						<select NAME="gthan">
							<option VALUE="0" {gthan_selected0}> less than
							<option VALUE="1" {gthan_selected2}> greater than
						</select>
						<input TYPE="text" NAME="size" SIZE="5" value="{value_size}"> KiloBytes
					</td>
				</tr>
				<tr>
					<td>
						If mail header: 
						<input TYPE="text" NAME="field" SIZE="20" value="{value_field}"> 
						contains: 
						<input TYPE="text" NAME="field_val" SIZE="30" value="{value_field_val}">
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td CLASS="main">
			<table WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
				<tr>
					<td CLASS="heading">
						ACTIONS:
					</td>
					<td CLASS="heading">
						&nbsp;
					</td>
				</tr>
				<tr>
					<td>
						<input TYPE="radio" NAME="action" VALUE="folder" {checked_action_folder}> File Into:
					</td>
					<td>
						<select NAME="folder">
	<option VALUE="INBOX">INBOX</option>

	<option VALUE="INBOX.AVM B1">INBOX.AVM B1</option>
	<option VALUE="INBOX.Asterisk">INBOX.Asterisk</option>
	<option VALUE="INBOX.Bugtraq">INBOX.Bugtraq</option>
	<option VALUE="INBOX.Clam AV">INBOX.Clam AV</option>
	<option VALUE="INBOX.Courier-Users">INBOX.Courier-Users</option>
	<option VALUE="INBOX.EGroupware-Devel">INBOX.EGroupware-Devel</option>

	<option VALUE="INBOX.EGroupware-Privat">INBOX.EGroupware-Privat</option>
	<option VALUE="INBOX.EGroupware-Users">INBOX.EGroupware-Users</option>
	<option VALUE="INBOX.Freeswan">INBOX.Freeswan</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<input TYPE="radio" NAME="action" VALUE="address" {checked_action_address}> Forward to address:
					</td>
					<td>
						<input TYPE="text" NAME="address" SIZE="40" value="{value_address}">
					</td>
				</tr>
				<tr>
					<td>
						<input TYPE="radio" NAME="action" VALUE="reject" {checked_action_reject}> Send a reject message:
					</td>
					<td>
						<textarea NAME="reject" ROWS="3" COLS="40" WRAP="hard" TABINDEX="14">{value_reject}</textarea>
					</td>
				</tr>
				<tr>
					<td>
						<input TYPE="radio" NAME="action" VALUE="discard" {checked_action_discard}> Discard the message.
					</td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td CLASS="main">
			<table WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
				<tr>
					<td>
						<a href="{url_back}">{lang_back}</a>
					</td>
					<td CLASS="options" style="text-align : right;">
						<a CLASS="option" HREF="javascript:SubmitForm('save');" onmouseover="window.status='Save Changes';" onmouseout="window.status='';">Save Changes</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<input type="hidden" name="ruleID" value="{value_ruleID}">
</form>
<!-- END main -->
	