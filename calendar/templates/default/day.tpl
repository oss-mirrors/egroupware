<!-- $Id$ -->
<!-- BEGIN day -->
{printer_friendly}
<table border="0" width="100%">
 <tr>
  <td valign="top" width="70%">
   <table border="0" width=100%>
    <tr>
     <td class="calendar_header">
      {date}&nbsp;[{username}]<br />
     </td>
    </tr>
	{day_events}
   </table>
   <p align="center">{print}</p>
  </td>
  <td align="center" valign="top">
   <table width="100%">
    <tr>
     <td align="center">
{small_calendar}
     </td>
    </tr>
    <tr>
     <td align="center">
      <div class="th">
       <p style="margin: 0px; padding: 2px;"><b>{lang_todos}</b></p>
{todos}
      </div>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
<!-- END day -->
<!-- BEGIN day_event -->
    <tr>
     <td>
{daily_events}
     </td>
    </tr>
<!-- END day_event -->

