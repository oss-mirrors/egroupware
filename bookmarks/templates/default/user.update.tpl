<!-- $Id$ -->
<form method="post" action="{FORM_ACTION}">
<input type="hidden" name="name" value="{NAME}">
<table border=0 bgcolor="#EEEEEE" align="center">
 <tr>
  <td>User Name</td>
  <td><b>{USERNAME}</b></td>
 </tr>
 <tr>
  <td>Name (First Last)</td>
  <td><input type="text" name="name" size=32 maxlength=50 value="{NAME}"></td>
 </tr>
 <tr>
  <td>E-Mail</td>
  <td><input type="text" name="email" size=32 maxlength=50 value="{EMAIL}"></td>
 </tr>
 <tr>
  <td>Password</td>
  <td><input type="password" name="password" size=32 maxlength=32 value="">
  <br><small>Leave password blank to leave password unchanged</small></td>
 </tr>
{PERMS}
 <tr>
  <td>Default Public</td>
  <td><input type="checkbox" name="default_public" {DEFAULT_PUBLIC_CHECKED}></td>
 </tr>
 <tr>
  <td>Include Public</td>
  <td><input type="checkbox" name="include_public" {INCLUDE_PUBLIC_CHECKED}></td>
 </tr>
 <tr>
  <td colspan=2 align=right>
   <input type="submit" name="bk_cancel_update" value="Cancel">
   <input type="submit" name="bk_user_update"   value="Update User">
  </td>
 </tr>
 <tr>
  <td colspan=2 align=left>
   <input type="submit" name="bk_set_public"  value="Set All Bookmarks to Public">
   <input type="submit" name="bk_set_private" value="Set All Bookmarks to Private">
  </td>
 </tr>
</table>
</form>
