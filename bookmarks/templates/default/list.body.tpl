<!-- $Id$ -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 <tr bgcolor="{th_bg}">
  <td align="left">
  &nbsp;{messages}

<!--
   {lang_filter_by}
   <form action="{filter_action}" method="POST">
    <select name="filter_type">
     <option>{lang_none}</option>
     <option>{lang_date_added}</option>
     <option>{lang_date_changed}</option>
     <option>{lang_date_last_visited}</option>
     <option>{lang_url}</option>
     <option>{lang_name}</option> 
    </select>
    <select name="filter_direction">
     <option value="asc">{lang_asc}</option>
     <option value="desc">{lang_desc}</option>
    </select>
    <input type="submit" value="{lang_filter}">
   </form> --> &nbsp;
  </td>

  <td align="right">
   {next_matchs_left}
   &nbsp;&nbsp;
   {next_matchs_right}&nbsp; &nbsp;
  </td>
 </tr>
</table>
<p>

{BOOKMARK_LIST}
