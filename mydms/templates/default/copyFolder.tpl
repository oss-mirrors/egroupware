<!-- BEGIN main -->
<script type='text/javascript'>
	var folderChooserURL ='{folderChooserURL}';
</script>

<form name="copy_form" method="post" action="{form_action}">
<fieldset><legend>{lang_target_folder}</legend>
	<table border="0" cellspacing="0" cellpadding="5" width="100%">
		<tr class="row_on">
			<td class="description" valign="top" width="150px">{lang_folder}:</td>
			<td class="infos">
				<input id="targetname" style="width: 100%;" type="text" name="targetname" value="{foldername}" disabled>
				<input type="hidden" id="targetid" name="targetid" value="{folderid}">
			</td>
			<td align="center" width="150px"><a href="#" onclick="selectFolder(1,'copy_form'); return(false);" style="font-size:10px;">{lang_select_target_folder}...</a></td>
		</tr>
		<tr class="row_on">
			<td class="description" valign="top" width="150px">{lang_name}:</td>
			<td class="infos">
				<input id="newfoldername" style="width: 100%;" type="text" name="newfoldername" value="{newfoldername}">
			</td>
			<td align="center" width="150px"><a href="#" onclick="selectFolder(1,'copy_form'); return(false);" style="font-size:10px;">&nbsp;</td>
		</tr>
	</table>
</fieldset>
<p>
<fieldset><legend>{lang_copy_options}</legend>
	<table border="0" cellspacing="0" cellpadding="5" width="100%">
		<tr class="row_off">
			<td class="infos">
				<input type="checkbox" name="copy_subfolder" id="copy_subfolder"> <label for="copy_subfolder">{lang_copy_subfolders}</label>
			</td>
		</tr>
		<tr class="row_off">
			<td class="infos">
				<input type="checkbox" name="copy_documents" id="copy_documents" disabled> <label for="copy_documents">{lang_copy_documents}</label>
			</td>
		</tr>
	</table>
</fieldset>
<p>
	<table border="0" cellspacing="0" cellpadding="5" width="100%">
		<tr class="row_off">
			<td class="infos">
				<button type="submit" name="cancel">{lang_cancel}</button>
				<button type="submit" name="copy">{lang_copy}</button>
			</td>
		</tr>
	</table>
</form>
<!-- END main -->
