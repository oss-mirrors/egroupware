<!-- $Id $ -->
<!-- BEGIN row.tpl -->
   <tr bgcolor="{tts_row_color}">
     <td align=center><a href="{tts_ticketdetails_link}">
     {tts_ticket_id}
     </a></td>
     <td align=left><font size=-2>{tts_t_priostr}</font></td>
     <td align=center>{tts_t_catstr}</td>
     <td align=center>{tts_t_assignedto}</td>
     <td align=center>{tts_t_user}</td>
     <td align=center>{tts_t_timestampopened}</td>
     {tts_col_ifviewall}
     <td align=center>{tts_t_subject}</td>
   </tr>
<!-- END row.tpl -->
