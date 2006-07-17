<!-- $Id$ -->
<!-- BEGIN tts_list -->
  <table width="100%" cellspacing="1" cellpadding="1" border="0">
   <tr style="background-color:{tts_head_bgcolor}">
    <td align="center">{tts_head_id}</td>
    <td align="center">{tts_head_subject}</td>
    <td align="center">{tts_head_duedate}</td>
    <td align="center">{tts_head_openedby}</td>
   </tr>
   {rows}
  </table>
<!-- END tts_list -->

<!-- BEGIN tts_row -->
   <tr class="ticketrow" style="background-color:{tts_row_color}">
     <td align="center"><a href="{tts_ticketdetails_link}">{tts_ticket_id}</a></td>
     <td align="left"><a href="{tts_ticketdetails_link}">{tts_t_subject}</a></td>
     <td align="center">{tts_t_duedate}</td>
     <td align="center">{tts_t_user}</td>
     {tts_col_status}
   </tr>
<!-- END tts_row -->

<!-- BEGIN tts_ticket_id_unread -->
&gt;{tts_t_id}&lt;
<!-- END tts_ticket_id_unread -->

<!-- BEGIN tts_ticket_id_read -->
{tts_t_id}
<!-- END tts_ticket_id_read -->
