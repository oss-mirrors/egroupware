<!-- BEGIN form -->
 <form method="POST" action="{form_action}">
	<table border="0" width="100%" align="center">
		<tr class="th">
			<td colspan="3">
				<b>{lang_samba_config}</b>
			</td>
		</tr>
		<tr class="row_on">
			<td width="200">{lang_displayname}</td>
			<td colspan="2">
				<input name="displayname" value="{displayname}" style="width:99%;">
			</td>
		</tr>
		<tr class="row_off">
			<td width="200">{lang_homepath}</td>
			<td colspan="2">
				<input name="sambahomepath" value="{sambahomepath}" style="width:99%;">
			</td>
		</tr>
		<tr class="row_on">
			<td width="200">{lang_homedrive}</td>
			<td colspan="2">
				<input name="sambahomedrive" value="{sambahomedrive}" style="width:99%;">
			</td>
		</tr>
		<tr class="row_off">
			<td width="200">{lang_logonscript}</td>
			<td colspan="2">
				<input name="sambalogonscript" value="{sambalogonscript}" style="width:99%;">
			</td>
		</tr>
		<tr class="row_on"">
			<td width="200">{lang_profilepath}</td>
			<td colspan="2">
				<input name="sambaprofilepath" value="{sambaprofilepath}" style="width:99%;">
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" name="save" value="{lang_save}">
				<input type="submit" name="apply" value="{lang_apply}">
				<input type="submit" name="cancel" value="{lang_cancel}">
			</td>
		</tr>
	</table>
 </form>
<!-- END form -->
