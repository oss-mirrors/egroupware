<!-- $Id$ -->
<!-- BEGIN delete_state.tpl -->
<p><b>{lang_delete_state}</b>
<hr><p>

{messages}

<!-- BEGIN tts_list -->
<P><B>{lang_are_you_sure}</B></P>
<P><B>{lang_tickets_in_state}</B></P>
<table width="98%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr bgcolor="{tts_head_bgcolor}">
		<td width="22">&nbsp;</td>
		<td align="center">{tts_head_ticket}</td>
		<td align="center">{tts_head_subject}</td>
		<td align="center">{tts_head_state}</td>
		<td align="center">{tts_head_dateopened}</td>
		<td align="center">{tts_head_group}</td>
		<td align="center">{tts_head_category}</td>
		<td align="center">{tts_head_assignedto}</td>
		<td align="center">{tts_head_openedby}</td>
		<td align="center">{tts_head_status}</td>
	</tr>
	{rows}
</table>
<!-- END tts_list -->
<!-- BEGIN form -->
<b>{lang_viewjobdetails}</b>
<hr><p>

<center><font color=red>{messages}</font></center>

<form method="POST" action="{delete_state_link}">
<table border="0" width="80%" cellspacing="0" align="center">
        <tr bgcolor="{row_off}">
                <td colspan="4"><input name="ticket[state]" type="radio" value="-1" CHECKED>{lang_delete_the_tickets}</td>
        </tr>
<!-- BEGIN update_state_items -->
        <tr bgcolor="{row_off}">
                <td colspan="4"><input name="ticket[state]" type="radio" value="{update_state_value}">{update_state_text}</td>
        </tr>
<!-- END update_state_items -->

	<tr bgcolor="{row_off}">
		<td align="left"><input name="ticket[state]" type="radio" value="-2" CHECKED>{lang_irregular_move_into_state}:&nbsp; &nbsp;<select name="ticket[newstate]">{options_state}</select></b></td>
		<td align="left">&nbsp;</td>
		<td align="left">&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td>&nbsp;</td>
		<td align="center"><input type="submit" value="{lang_ok}" name="submit"></td>
		<td align="right"><input type="submit" name="cancel" value="{lang_cancel}"></td>
		<td colspan="2">&nbsp;</td>
	</tr>

   </table>
</form>
<!-- END form -->

<!-- END delete_state.tpl -->

<!-- BEGIN tts_row -->
	<tr bgcolor="{tts_row_color}">
		<td width="22">{row_status}</td>
		<td align="center">{row_ticket_id}</td>
		<td align="center">{tts_t_subject}</td>
		<td align="center">{tts_t_state}</td>
		<td align="center">{tts_t_timestampopened}</td>
		<td style="font-size=12" align=center>{row_group}</td>
		<td style="font-size=12" align=center>{row_category}</td>
		<td align="center">{tts_t_assignedto}</td>
		<td align="center">{tts_t_user}</td>
		{tts_col_status}
	</tr>
<!-- END tts_row -->

<!-- BEGIN tts_col_ifviewall -->
  <td align=center>{tts_t_timestampclosed}</td>
<!-- END tts_col_ifviewall -->

<!-- BEGIN tts_head_ifviewall -->
    <td align=center>{tts_head_dateclosed}</td>
<!-- END tts_head_ifviewall -->
