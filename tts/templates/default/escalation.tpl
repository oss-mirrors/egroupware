<!-- $Id$ -->
<!-- BEGIN escalation.tpl -->

<br>
<!-- BEGIN escalation_list -->
<table width="98%" cellspacing="1" cellpadding="3" border="0" align="center">
	<tr class="th">
		<td align="center">{tts_head_escalation_id}</td>
		<td>{tts_head_group_name}</td>
        <td>{tts_head_priority_1}</td>
        <td>{tts_head_priority_2}</td>
        <td>{tts_head_time_1}</td>
        <td>{tts_head_time_2}</td>
        <td>{tts_head_time_3}</td>
        <td>{tts_head_email_1}</td>
        <td>{tts_head_email_2}</td>
		<td align="center">{lang_edit}</td>
		<td align="center">{lang_delete}</td>
	</tr>
	{rows}
	<tr class="{row_class}">
		<td colspan=9>&nbsp;</td>
		<td align="center"><A HREF="{tts_escalation_add_link}">[{lang_add}]</A></td>
		<td >&nbsp;</td>
	</tr>
</table>
<br>
<!-- END escalation_list -->

<!-- END escalation.tpl -->

<!-- BEGIN escalation_row -->
	<tr class="{row_class}">
		<td align="center">{escalation_id}</td>
		<td>{group_name}</td>
        <td>{priority_1}</td>
        <td>{priority_2}</td>
        <td>{time_1}</td>
        <td>{time_2}</td>
        <td>{time_3}</td>
        <td>{email_1}</td>
        <td>{email_2}</td>
		<td align="center"><A HREF="{tts_escalation_edit_link}">[{lang_edit}]</A></td>
		<td align="center"><A HREF="{tts_escalation_delete_link}">[{lang_delete}]</A></td>
	</tr>
<!-- END escalation_row -->
