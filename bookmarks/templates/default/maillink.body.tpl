<!-- $Id$ -->
<form method="post" action="{FORM_ACTION}">
 <table border="0" bgcolor="#EEEEEE" align="center">
  <tr>
   <td>{lang_from}:</td>
   <td><strong>{FROM_NAME} &lt;{FROM}&gt;</strong></td>
  </tr>

  <tr>
   <td>{lang_to} <!-- To E-Mail Addresses --></td>
   <td><input type="text" name="to" size="60" maxlength="255" value="{TO}"><br><small>(comma separate multiple addresses)</small></td>
  </tr>

  <tr>
   <td>{lang_subject}</td>
   <td><input type="text" name="subject" size="60" maxlength="255" value="{SUBJECT}"></td>
  </tr>
 
  <tr>
   <td>{lang_message}</td>
   <td><TEXTAREA NAME="message" WRAP="physical" COLS="60" ROWS="6">{MESSAGE}</TEXTAREA></td>
  </tr>

  <tr>
   <td colspan="2" align="center">
   <input type="submit" name="send" value="{lang_send}">
  </td>
 </tr>
</table>
</form>
