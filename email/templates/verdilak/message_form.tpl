<form enctype="multipart/form-data" name="doit" action="{send_link}" method="POST">
 <input type="hidden" name="return" value="<?php echo $folder ?>">
 <table border=0 cellpadding="1" cellspacing="1" width="95%" align="center">
  <tr>
   <td colspan="2" bgcolor="">

     <table border="0" cellpadding="4" cellspacing="1" width="100%">
      <tr>
       <td align="left" bgcolor="">
        <input type="button" value="{lang_addressbook}" onclick="addressbook();">
       </td>
       <td align="right" bgcolor="">
        <input type="submit" value="{lang_send}">
       </td>
      </tr>
     </table>

   </td>
  </tr>

  <tr>
   <td bgcolor=""><b>&nbsp;{lang_to}:</b></td>
   <td bgcolor="" width="570">{mail_to}</td>
  </tr>

  <tr>
   <td bgcolor=""><b>&nbsp;{lang_from}:</b></td>
   <td bgcolor="" width="570">{email_from}</td>
  </tr>

  <tr>
   <td bgcolor=""><b>&nbsp;{lang_cc}:</b></td>
   <td bgcolor="" width="570">{email_cc}</td>
  </tr>

  <tr>
   <td bgcolor=""><b>&nbsp;{lang_subject}:&nbsp;</b></td>
   <td bgcolor="" width="570">{email_subject}</td>
  </tr>

  {email_sig}

  <tr>
   <td bgcolor="" colspan="2">
    <textarea name="body" cols="84" rows="15" wrap="hard">{body}</textarea>
   </td>
  </tr>
 </table>
</form>

{compose_js}

<script>
  document.doit.body.focus();
  if (document.doit.subject.value == "") {
     document.doit.subject.focus();
  }
  if (document.doit.to.value == "") {
     document.doit.to.focus();
  }
</script>

