<!-- $Id$ -->


<!-- BEGIN tts_title -->
<center>{tts_appname}</center><p />
<!-- END tts_title -->


<!-- BEGIN tts_links -->
[&nbsp;<a href="{tts_newticket_link}">{tts_newticket}</a>&nbsp;|&nbsp;<a href="{tts_changeview_link}">{tts_changeview}</a>&nbsp;]<br>
<!-- END tts_links -->


<!-- BEGIN tts_list_start -->
<center>{tts_numtotal}<br />{tts_numopen}</center><p />
<center>{tts_notickets}</center>
<center><br />
  <table CELLSPACING=1 CELLPADDING=1 BORDER=0>
   <tr bgcolor="{tts_head_bgcolor}">
    <td align=center>{tts_head_ticket}</td>
    <td align=center>{tts_head_prio}</td>
    <td align=center>{tts_head_group}</td>
    <td align=center>{tts_head_assignedto}</td>
    <td align=center>{tts_head_openedby}</td>
    <td align=center>{tts_head_dateopened}</td>
    {tts_head_ifviewall}
    <td align=center>{tts_head_subject}</td>
   </tr>
<!-- END tts_list_start -->


<!-- BEGIN tts_head_ifviewall -->
    <td align=center>{tts_head_dateclosed}{/td>
    <td align=center>{tts_head_status}{/td>
<!-- END tts_head_ifviewall -->


<!-- BEGIN tts_row -->
   <tr bgcolor="{tts_row_color}">
     <td align=center><a href="{tts_ticketdetails_link}">{tts_ticket_id}</a></td>
     <td align=left><font size=-2>{tts_t_priostr}</font></td>
     <td align=center>{tts_t_catstr}</td>
     <td align=center>{tts_t_assignedto}</td>
     <td align=center>{tts_t_user}</td>
     <td align=center>{tts_t_timestampopened}</td>
     {tts_row_ifviewall}
     <td align=center>{tts_t_subject</td>
   </tr>
<!-- END tts_row -->


<!-- BEGIN tts_ticket_id_unread -->
&gt;{tts_f_id}&lt;
<!-- END tts_ticket_id_unread -->


<!-- BEGIN tts_ticket_id_read -->
{tts_f_id}
<!-- END tts_ticket_id_read -->

		
<!-- BEGIN tts_row_ifviewall -->
  <td align=center>{tts_t_timestampclosed}</td>
  <td align=center>{tts_t_status}</td>
<!-- END tts_row_ifviewall -->


<!-- BEGIN tts_list_end -->
  </table>
</center>
<!-- END tts_list_end -->





