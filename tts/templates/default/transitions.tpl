<!-- $Id$ -->
<!-- BEGIN transitions.tpl -->

<p>
<b>{lang_list_of_transitions}</b>
<hr><p>


<!-- BEGIN transition_list -->
<table width="98%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr bgcolor="{tts_head_bgcolor}">
		<td align="center">{tts_head_transition_id}</td>
		<td align="center">{tts_head_transition}</td>
		<td align="center">{tts_head_source_state}</td>
		<td align="center">{tts_head_target_state}</td>
		<td align="center">{tts_head_description}</td>
		<td align="center">{lang_edit}</td>
		<td align="center">{lang_delete}</td>
	</tr>
	{rows}
	<tr bgcolor="{tts_row_color}">
		<td colspan="5">&nbsp;</td>
		<td align="center"><A HREF="{tts_transitionadd_link}">[{lang_add}]</A></td>
		<td align="center">&nbsp;</td>
	</tr>
</table>
<!-- END transition_list -->

<!-- END transitions.tpl -->

<!-- BEGIN transition_row -->
	<tr bgcolor="{tts_row_color}">
		<td align="center">{transition_id}</td>
		<td align="center">{transition_name}</td>
		<td align="center">{transition_source_state}</td>
		<td align="center">{transition_target_state}</td>
		<td align="center">{transition_description}</td>
		<td align="center"><A HREF="{tts_transitionedit_link}">[{lang_edit}]</A></td>
		<td align="center"><A HREF="{tts_transitiondelete_link}">[{lang_delete}]</A></td>
	</tr>
<!-- END transition_row -->
