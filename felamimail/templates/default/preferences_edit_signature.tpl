<!-- BEGIN main -->
<table style="width:100%; background-color:white;">
<tr>
<td style="width:80px;">
{lang_description}
</td>
<td>
<input id="signatureDesc" type="text" style="width:100%;" value="{description}">
</td>
</tr>
</table>
{tinymce}
<button type="button" onclick="fm_saveSignature()">{lang_save}</button>
<button type="button" onclick="fm_applySignature()">{lang_apply}</button>
<button type="button" onclick="window.close()">{lang_cancel}</button>
<input type="hidden" id="signatureID" value="{signatureID}">
<script language="JavaScript1.2">
	document.getElementById('description').focus();
</script>
<!-- END main -->
