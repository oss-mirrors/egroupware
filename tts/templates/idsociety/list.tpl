<!-- $Id $ -->
<!-- BEGIN list.tpl -->
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
   {rows}
  </table>
</center>
<!-- END list.tpl -->
