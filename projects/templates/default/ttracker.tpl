<!-- $Id$ -->

{app_header}
<center>{message}</center>
<table border="0" width="100%" cellpadding="2" cellspacing="2">
	<form method="POST" action="{action_url}">
		<tr bgcolor="{row_on}">
			<td valign="top">{lang_activity}:</td>
			<td valign="top" colspan="2">

<!-- BEGIN act_own -->

				<input type="text" name="values[hours_descr]" size="20" value="{hours_descr}">

<!-- END act_own -->
<!-- BEGIN activity -->

				<select name="values[activity_id]"><option value="">{lang_select_activity}</option>{activity_list}</select>

<!-- END activity -->

			</td>
			<td valign="top">{lang_costtype}:</td>
			<td colspan="2">
<!-- BEGIN cost -->
				<select name="values[cost_id]">{cost_list}</select>
<!-- END cost -->
			</td>
		</tr>
		<tr bgcolor="{row_on}">
			<td valign="top">{lang_remark}:</td>
			<td colspan="5" align="center"><textarea style="width:99%;" name="values[remark]" rows="4" wrap="VIRTUAL">{remark}</textarea></td>
		</tr>
		<tr bgcolor="{row_off}">
			<td>&nbsp;</td>
			<td colspan="2">{lang_date}</td>
			<td>{lang_time}</td>
			<td colspan="2">{lang_action}</td>
		</tr>
		<tr bgcolor="{row_off}">
			<td>{lang_ttracker}:</td>
			<td colspan="2">{curr_date}</td>
			<td>{curr_time}</td>
			<td colspan="2">
				<input type="submit" name="values[start]" value="{lang_start}">&nbsp;
				<input type="submit" name="values[pause]" value="{lang_pause}">&nbsp;
				<input type="submit" name="values[continue]" value="{lang_continue}">&nbsp;
				<input type="submit" name="values[stop]" value="{lang_stop}">
			</td>
		</tr>
		<tr height="15">
			<td>&nbsp;</td>
		</tr>
		<tr bgcolor="{row_on}">
			<td>{lang_manuell_entries}:</td>
			<td colspan="2">{start_date_select}</td>
			<td>
				<input type="text" size="2" name="values[hours]" maxlenght="2" value="{hours}">:
				<input type="text" size="2" name="values[minutes] "maxlenght="2" value="{minutes}">&nbsp;[hh:mm]
			</td>
			<td colspan="2"><input type="submit" name="values[apply]" value="{lang_apply}"></td>
		</tr>
		<tr bgcolor="{row_on}">
			<td>&nbsp;</td>
			<td>{lang_distance}:</td>
			<td><input type="text" name="values[km_distance]" value="{km_distance}" size="6"></td>
			<td>{lang_time_of_journey}:</td>
			<td colspan="2"><input type="text" name="values[t_journey]" value="{t_journey}" size="6">&nbsp;[hh:mm]</td>
		</tr>
		<tr height="15">
			<td>&nbsp;</td>
		</tr>
		<tr bgcolor="{th_bg}" width="100%">
			<td>{lang_entry}</td>
			<td>{lang_activity}</td>
			<td>{lang_from}</td>
			<td>{lang_till}</td>
			<td>{lang_hours}</td>
			<td align="center">{lang_select}</td>
		</tr>

<!-- BEGIN ttracker -->

		<tr bgcolor="{th_bg}">
			<td colspan="5">{project_title}</td>
			<td align="center"><input type="radio" name="values[project_id]" value="{project_id}" {radio_checked}></td>
		</tr>
		{thours_list}
		<tr height="5">
			<td>&nbsp;</td>
		</tr>
<!-- END ttracker -->

		<tr height="50" valign="bottom">
			<td colspan="6"><input type="submit" name="values[save]" value="{lang_save}"></td>
		</tr>
	</form>
</table>

<!-- BEGIN ttracker_list -->

		<tr bgcolor="{tr_color}">
			<td>{statusout}: {apply_time}</td>
			<td><a href="{edit_url}">{hours_descr}</a></td>
			<td>{start_time}</td>
			<td>{end_time}</td>
			<td>{wh}</td>
			<td align="center"><a href="{delete_url}"><img src="{delete_img}" border="0" title="{lang_delete}"></a></td>
		</tr>

<!-- END ttracker_list -->
