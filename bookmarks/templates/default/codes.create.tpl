<!-- $Id$ -->
<table border=0 bgcolor="#EEEEEE" align="center">
 <form method="post" action="{FORM_ACTION}">
 <tr>
  <td>ID<br>
	<font size=-1>defaults to next available ID</font></td>
  <td><input type="text" name="id" size=30 maxlength=10 value="{DEFAULT_ID}"></td>
 </tr>
 <tr>
  <td>Name</td>
  <td><input type="text" name="name" size=30 maxlength=30 value=""></td>
 </tr>
 <tr>
  <td colspan=2 align=center>
   <input type="submit" name="bk_cancel_create" value="Cancel">
   <input type="submit" name="bk_code_create" value="Create {CODETABLE}">
  </td>
 </tr>
 </form>
</table>
