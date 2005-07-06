<!-- BEGIN list -->
<p>
 <table border="0" width="45%" align="center">
  <tr>
   {left_next_matchs}
   <td align="center">{lang_groups}</td>
   {right_next_matchs}
  </tr>
 </table>

 <table border="0" width="45%" align="center">
  <tr class="th">
   <td>{sort_name}</td>
   <td>{header_edit}</td>
   <td>{header_delete}</td>
  </tr>

  {rows}

 </table>

 <table border="0" width="45%" align="center">
  <tr>
   <td align="left">
    <form method="POST" action="{new_action}">
     {input_add}
    </form>
   </td>
   <td align="right">
    <form method="POST" action="{search_action}">
     {input_search}
    </form>
   </td>
  </tr>
 </table>
<!-- END list -->

<!-- BEGIN row -->
 <tr class="{class}">
  <td>{group_name}</td>
  <td class="narrow_column">{edit_link}</td>
  <td class="narrow_column">{delete_link}</td>
 </tr>
<!-- END row -->

<!-- BEGIN row_empty -->
   <tr>
    <td colspan="5" align="center">{message}</td>
   </tr>
<!-- END row_empty -->
