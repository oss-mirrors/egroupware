<!-- BEGIN main -->
<form action="{form_action}" method="post" name="folderList">
<table border="0" width="100%">
	<tr>
		<td align="left">
			{lang_folder_name}
		</td>
		<td align="center">
			<select name="foldername" onchange="document.folderList.submit()">
				{select_rows}
			</select>
		</td>
		<td align="right">
			<input type="submit" value="{lang_select}" name="selectFolder">
		</td>
	</tr>
</table>
</form>

<form action="{form_action}" method="post" name="subscribeList">
<input type="hidden" name="foldername" value="{folderName}">
<table border="0" width="100%">
	<tr>
		<td align="left">
			{lang_folder_status}
		</td>
		<td align="center">
			<input type="radio" name="folderStatus" value="subscribe" onchange="document.subscribeList.submit()" id="subscribed" {subscribed_checked}>
			<label for="subscribed">{lang_subscribed}</label> 
			<input type="radio" name="folderStatus" value="unsubscribe" onchange="document.subscribeList.submit()" id="unsubscribed" {unsubscribed_checked}>
			<label for="unsubscribed">{lang_unsubscribed}</label> 
		</td>
		<td align="right">
			<noscript><input type="submit" value="{lang_update}" name="un_subscribe"></noscript>
		</td>
	</tr>
</table>
</form>

<table border="0" width="100%">
	<tr>
		<td>
			{lang_quota_status}
		</td>
		<td>
			xxx
		</td>
	</tr>
</table>
<!-- END main -->

<!-- BEGIN select_row -->
				<option value="{folder_name}" {selected}>({subscribed}) {folder_name}</option>
<!-- END select_row -->

