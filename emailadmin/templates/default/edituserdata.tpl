<!-- BEGIN form -->
 <form method="POST" action="{form_action}">
  <center>
	<table border="0" width="95%">
		<tr>
			<td valign="top" width="150"> 
				<table border="0" width="100%">
					<tr bgcolor="{th_bg}">
						<td colspan="1">&nbsp;</td>
					</tr>
					{rows}
				</table>
			</td>
			<td>
				<table border=0 width=100%>
					<tr bgcolor="{th_bg}">
						<td colspan="4">
							<b>{lang_email_config}</b>
							<input type="hidden" name="qmailldap_uid" value="{uid}">
						</td>
					</tr>
					<tr bgcolor="{tr_color1}">
						<td width="150">{lang_emailAddress}</td>
						<td>
							<input name="qmailldap_emailAddress" value="{emailAddress}" size=25>
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td>{lang_alternateEmailAddress}</td>
						<td>
							<input name="qmailldap_alternateEmailAddress" value="{alternateEmailAddress}" size=25>
						</td>
					</tr>
					<tr bgcolor="{tr_color1}">
						<td>
							{lang_emailaccount_active}
						</td>
						<td>
							<input type="checkbox" name="qmailldap_emailaccount_active" {account_checked}>
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td align="left">
							<a href={link_back}>{lang_ready}</a>
						</td>						
						<td align="right">
							<input type="submit" name="save" value="{lang_button}">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
  </center>
 </form>
<!-- END form -->

<!-- BEGIN link_row -->
					<tr bgcolor="{tr_color}">
						<td colspan="2">&nbsp;&nbsp;<a href="{row_link}">{row_text}</a></td>
					</tr>
<!-- END link_row -->
