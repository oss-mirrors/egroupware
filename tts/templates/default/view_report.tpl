<!-- $Id$ -->
<!-- BEGIN view_report_stat.tpl -->

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
                <table>
                <tr>
                    <td align="right" width="25%">{label_report_type}</td>
                    <td align="left"><select name="f_type">{options_f_type}</select></td>
                </tr>
                <tr>
                    <td align="right" width="25%">{label_startdate}</td>
                    <td align="left">{f_startdate}</td>
                </tr>
                <tr>
                    <td align="right" width="25%">{label_enddate}</td>
                    <td align="left">{f_enddate}

                    </td>

                </tr>

                </table>
                <input type="submit" value="{lang_search}">

			</form>
		</td>
		{right}

		<td align="right" width="40%">

            <form action="{tts_search_link}" method="POST" name="search">
                <input type="hidden" name="filter" value="search">

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
		<td align="center">{tts_head_category_name}</td>

        {tts_head_group}

		<td align="center">{tts_head_count_ticket}</td>

		{tts_head_counts}


	</tr>
	{rows}
    {rows_total}
</table>
<br>
<!-- END tts_list -->

<!-- END view_report.tpl -->

<!-- BEGIN tts_row -->
	<tr bgcolor="{tts_row_color}">
		<td width="22">{row_status}</td>
		<td align="center">{tts_category_name}</td>

        {tts_col_group}

		<td align="center">{tts_number_of_tickets}</td>

        {tts_col_counts}



	</tr>
<!-- END tts_row -->


<!-- BEGIN tts_row_total -->
    <tr bgcolor="{tts_row_total_color}">
        <td width="22">{row_total_status}</td>
        <td align="center">{tts_total_category_name}</td>

        {tts_total_col_group}

        <td align="center">{tts_total_number_of_tickets}</td>

        {tts_total_col_counts}



    </tr>
<!-- END tts_row_total -->


<!-- BEGIN tts_col_ifviewall_1 -->
    <td align=center>{tts_num_open_tickets}</td>
    <td align=center>{tts_num_initiative_tickets}</td>
    <td align=center>{tts_num_closed_tickets}</td>
    <td align=center>{tts_num_prio_low}</td>
    <td align=center>{tts_num_prio_medium}</td>
    <td align=center>{tts_num_prio_high}</td>
<!-- END tts_col_ifviewall_1 -->

<!-- BEGIN tts_head_ifviewall_1 -->
    <td align=center>{tts_head_num_open_tickets}</td>
    <td align=center>{tts_head_num_initiative_tickets}</td>
    <td align=center>{tts_head_num_closed_tickets}</td>
    <td align=center>{tts_head_num_prio_low}</td>
    <td align=center>{tts_head_num_prio_medium}</td>
    <td align=center>{tts_head_num_prio_high}</td>
<!-- END tts_head_ifviewall_1 -->

<!-- BEGIN tts_col_ifviewall_2 -->
    <td align=center>{tts_num_0}</td>
    <td align=center>{tts_num_1}</td>
    <td align=center>{tts_num_2}</td>
    <td align=center>{tts_num_3}</td>
    <td align=center>{tts_num_5}</td>
<!-- END tts_col_ifviewall_2 -->

<!-- BEGIN tts_head_ifviewall_2 -->
    <td align=center>{tts_head_num_0}</td>
    <td align=center>{tts_head_num_1}</td>
    <td align=center>{tts_head_num_2}</td>
    <td align=center>{tts_head_num_3}</td>
    <td align=center>{tts_head_num_5}</td>
<!-- END tts_head_ifviewall_2 -->



<!-- BEGIN tts_col_viewallgroup -->
    <td align=center>{tts_col_group_name}</td>
<!-- END tts_col_viewallgroup -->

<!-- BEGIN tts_head_viewallgroup -->
    <td align=center>{tts_head_group_name}</td>
<!-- END tts_head_viewallgroup -->




