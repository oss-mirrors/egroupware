<!-- $Id$ -->
<form method="post" action="{FORM_ACTION}">
 <table border=0 bgcolor="#EEEEEE" align="center">
 <tr>
  <td>From:</td>
  <td><strong>{FROM_NAME} &lt;{FROM}&gt;</strong></td>
 </tr>
 <tr>
  <td>To E-Mail Addresses</td>
  <td><input type="text" name="to" size=60 maxlength=255 value="{TO}"><br>
  <small>(comma separate multiple addresses)</small></td>
 </tr>
 <tr>
  <td>Subject</td>
  <td><input type="text" name="subject" size=60 maxlength=255 value="{SUBJECT}"></td>
 </tr>
 <tr>
  <td>Message</td>
  <td><TEXTAREA NAME="message" WRAP="physical" COLS="60" ROWS="6">{MESSAGE}</TEXTAREA></td>
 </tr>
 <tr>
  <td>Standard Footer</td>
  <td>{SITE_FOOTER}</td>
 </tr>
 <tr>
  <td colspan=2 align=center>
   <input type="submit" name="bk_send" value="mail-this-link">
  </td>
 </tr>
</table>
</form>
