<!-- $Id$ -->
<!-- BEGIN states.tpl -->

<p>
<b>{lang_list_of_states}</b>
<hr><p>

<!-- BEGIN state_list -->
<table width="98%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr bgcolor="{tts_head_bgcolor}">
		<td align="center">{tts_head_stateid}</td>
		<td align="center">{tts_head_state}</td>
		<td align="center">{tts_head_description}</td>
		<td align="center">{lang_edit}</td>
		<td align="center">{lang_delete}</td>
	</tr>
	{rows}
	<tr bgcolor="{tts_row_color}">
		<td colspan=3>&nbsp;</td>
		<td align="center"><A HREF="{tts_stateadd_link}">[{lang_add}]</A></td>
		<td >&nbsp;</td>
	</tr>
</table>
<!-- END state_list -->

<!-- END states.tpl -->

<!-- BEGIN state_row -->
	<tr bgcolor="{tts_row_color}">
		<td align="center">{state_id}</td>
		<td align="center">{state_name}</td>
		<td align="center">{state_description}</td>
		<td align="center"><A HREF="{tts_stateedit_link}">[{lang_edit}]</A></td>
		<td align="center"><A HREF="{tts_statedelete_link}">[{lang_delete}]</A></td>
	</tr>
<!-- END state_row -->
