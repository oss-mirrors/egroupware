<br>
{lang_error_messages}<br>

<form method="POST" action="{form_action}">
  <input name="sourcelang" type="hidden" value="{sourcelang}">
 <input name="targetlang" type="hidden" value="{targetlang}">

 <table border="0" align="center">
  <tr>
   <td>{lang_message_id}</td>
   <td>{message_id_field}</td>
  </tr>
  <tr>
   <td>{lang_app}</td>
   <td>{app_field}</td>
  </tr>
  <tr>
  <tr>
   <td>{lang_translation}</td>
   <td>{translation_field}</td>
  </tr>
   <td>{lang_target}</td>
   <td>{target_field}</td>
  </tr>
  <tr>
   <td>&nbsp;</td>
   <td>
    <input type="submit" name="add" value="{lang_add}"> &nbsp;
    <input type="submit" name="more" value="{lang_more}"> &nbsp;
    <input type="submit" name="cancel" value="{lang_cancel}">
   </td>
  </tr>
 </table>
</form>
