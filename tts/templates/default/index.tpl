<!-- $Id$ -->
<!-- BEGIN index.tpl -->

{autorefresh}

<!-- BEGIN tts_title -->
<center>{tts_appname}</center><p />
<!-- END tts_title -->

<!-- BEGIN tts_links -->
<center>[&nbsp;<a href="{tts_newticket_link}">{tts_newticket}</a>&nbsp;|&nbsp;<a href="{tts_prefs_link}">{lang_preferences}</a>&nbsp;|&nbsp;<a href="{tts_changeview_link}">{tts_changeview}</a>&nbsp;]<br></center>
<!-- END tts_links -->

<!-- BEGIN tts_search -->
<center>
<form action="{tts_search_link}" method="POST" name="search">
<input type="hidden" name="filter" value="search">
<input name="searchfilter" size="60" value="{tts_searchfilter}">
<input type="submit" value="{lang_search}">
</form>
<b>{tts_numfound}</b>
<br />
</center>
<!-- END tts_search -->

<!-- BEGIN tts_list -->
<center>{tts_numtotal}<br />{tts_numopen}</center><p />
<center>{tts_notickets}</center>
<center><br />
  <table width="90%" cellspacing="1" cellpadding="1" border="0">
   <tr bgcolor="{tts_head_bgcolor}">
    <td align=center>{tts_head_ticket}</td>
    <td align=center>{tts_head_prio}</td>
    <td align=center>{tts_head_group}</td>
    <td align=center>{tts_head_assignedto}</td>
    <td align=center>{tts_head_openedby}</td>
    <td align=center>{tts_head_dateopened}</td>
    {tts_head_status}
    <td align=center>{tts_head_subject}</td>
   </tr>
   {rows}
  </table>
</center>
<!-- END tts_list -->

<!-- END index.tpl -->

<!-- BEGIN tts_row -->
   <tr bgcolor="{tts_row_color}">
     <td align=center>{tts_ticket_id}</td>
     <td align=left><font size=-2>{tts_t_priostr}</font></td>
     <td align=center>{tts_t_catstr}</td>
     <td align=center>{tts_t_assignedto}</td>
     <td align=center>{tts_t_user}</td>
     <td align=center>{tts_t_timestampopened}</td>
     {tts_col_status}
     <td align=center>{tts_t_subject}</td>
   </tr>
<!-- END tts_row -->

<!-- BEGIN tts_ticket_id_unread -->
<img src="templates/default/images/updated.gif"><a href="{tts_ticketdetails_link}">{tts_t_id}</a>
<!-- END tts_ticket_id_unread -->

<!-- BEGIN tts_ticket_id_read -->
<a href="{tts_ticketdetails_link}">{tts_t_id}</a>
<!-- END tts_ticket_id_read -->

<!-- BEGIN tts_col_ifviewall -->
  <td align=center>{tts_t_timestampclosed}</td>
<!-- END tts_col_ifviewall -->

<!-- BEGIN tts_head_ifviewall -->
    <td align=center>{tts_head_dateclosed}</td>
<!-- END tts_head_ifviewall -->
