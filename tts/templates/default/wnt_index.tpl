<!-- $Id$ -->
<!-- BEGIN wnt_index.tpl -->

{autorefresh}

<!-- BEGIN tts_title -->
<!-- <center>{tts_appname}</center><p /> -->
<!-- END tts_title -->

<!-- BEGIN options_select -->
    <option value="{optionvalue}" {optionselected}>{optionname}</option>
<!-- END options_select -->

<!-- _BEGIN tts_links -->

<!-- _END tts_links -->

<!-- BEGIN tts_search -->
<center><font color=red>{messages}</font></center>

<table width="98%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr bgcolor="{th_bg}">
		<td colspan="3">{lang_appname}</td>
		<td align="center">{tts_numfound}</td>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td align="left"><a href="{tts_newticket_link}">{tts_newticket}</a>{tts_newticket_delimiter}<a href="{tts_prefs_link}">{lang_preferences}</a>&nbsp;&nbsp;</td>
		{left}
		<td align="center" width="15%">
			<form action="{tts_search_link}" method="POST" name="search">
				<select name="filter" onChange="this.form.submit();">{options_filter}</select>
                                <select name="f_status">{options_f_status}</select>
				<input type="submit" value="{lang_search}">

			</form>
		</td>
		{right}
		<td align="right">
			<form action="{tts_search_link}" method="POST" name="search">
				<input type="hidden" name="filter" value="search">
				<input name="searchfilter" value="{tts_searchfilter}">
				<input type="submit" value="{lang_search}">
			</form>
		</td>
	</tr>
</table>
<!-- END tts_search -->

<!-- BEGIN tts_list -->
<!--
<center>{tts_numtotal}<br />{tts_numopen}</center><p />
<center>{tts_notickets}</center>
-->
<table width="98%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr bgcolor="{tts_head_bgcolor}">
		<td width="22">&nbsp;</td>
		<td align="center">{tts_head_ticket}</td>
		<td align="center">{tts_head_subject}</td>
        <td align="center">{tts_head_caller_name}</td>
        <td align="center">{tts_head_caller_telephone}</td>
		<td align="center">{tts_head_dateopened}</td>

		{tts_head_status}
	</tr>
	{rows}
</table>
<br>
<!-- END tts_list -->

<!-- END index.tpl -->

<!-- BEGIN tts_row -->
	<tr bgcolor="{tts_row_color}">
		<td width="22">{row_status}</td>
		<td align="center">{row_ticket_id}</td>
		<td align="center">{tts_t_subject}</td>
		<td align="center">{tts_t_caller_name}</td>
		<td align="center">{tts_t_caller_telephone}</td>
		<td align="center">{tts_t_timestampopened}</td>

		{tts_col_status}
	</tr>
<!-- END tts_row -->

<!-- BEGIN tts_col_ifviewall -->
  <td align=center>{tts_t_timestampclosed}</td>
<!-- END tts_col_ifviewall -->

<!-- BEGIN tts_head_ifviewall -->
    <td align=center>{tts_head_dateclosed}</td>
<!-- END tts_head_ifviewall -->
