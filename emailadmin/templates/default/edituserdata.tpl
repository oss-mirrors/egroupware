<!-- BEGIN form -->
 <form method="POST" action="{form_action}">
  <center>
	<table border="0" width="95%">
		<tr>
			<td valign="top">
					{rows}
			</td>
			<td>
				<table border=0 width=100%>
					<tr bgcolor="{th_bg}">
						<td colspan="4">
							<b>{lang_email_config}</b>
						</td>
					</tr>
					<tr bgcolor="{tr_color1}">
						<td width="200">{lang_emailAddress}</td>
						<td colspan="2">
							<input name="mailLocalAddress" value="{mailLocalAddress}" size=35>
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td rowspan="4">{lang_mailAlternateAddress}</td>
						<td rowspan="4" align="center">
								{options_mailAlternateAddress}
						</td>
						<td align="center">
							<input type="submit" value="{lang_remove} -->" name="remove_mailAlternateAddress">
						</td>
					</tr>
					<tr bgcolor="{tr_color1}">
						<td>
							&nbsp;
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td align="center">
							<input name="mailAlternateAddressInput" value="{mailAlternateAddress}" size=35>
						</td>
					</tr>
					<tr bgcolor="{tr_color1}">
						<td align="center">
							<input type="submit" value="<-- {lang_add}" name="add_mailAlternateAddress">
						</td>
					</tr>
					<tr bgcolor="{tr_color1}">
						<td width="150">{lang_mailRoutingAddress}</td>
						<td colspan="2">
							<input name="mailRoutingAddress" value="{mailRoutingAddress}" size=35>
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td>
							{lang_emailaccount_active}
						</td>
						<td colspan="2">
							<input type="checkbox" name="accountStatus" {account_checked}>
						</td>
					</tr>
				</table>
				<table border=0 width=100%>
					<tr bgcolor="{th_bg}">
						<td colspan="3">
							<b>{lang_advanced_options}</b>
						</td>
					</tr>
					<tr bgcolor="{tr_color1}">
						<td width="200">
							{lang_qmaildotmode}
						</td>
						<td colspan="2">
							<select name="qmailDotMode">
								<option value="both" {selected_both}>both</option>
								<option value="dotonly" {selected_dotonly}>dotonly</option>
								<option value="ldaponly" {selected_ldaponly}>ldaponly ({lang_default})</option>
								<option value="ldapwithprog" {selected_ldapwithprog}>ldapwithprog</option>
								<option value="none" {selected_none}>none</option>
							</select>
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td width="150">{lang_deliveryProgramPath}</td>
						<td colspan="2">
							<input name="deliveryProgramPath" value="{deliveryProgramPath}" size=35>
						</td>
					</tr>
				</table>
				<table border=0 width=100%>
					<tr bgcolor="{tr_color1}">
						<td align="right" colspan="2">
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
