<!-- $Id$ -->
<form method="post" action="{FORM_ACTION}">
<table border=0 bgcolor="#EEEEEE" align="center">
 <tr>
  <td>User Name</td>
  <td><input type="text" name="username" size=32 maxlength=32 value="{USERNAME}"></td>
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
  <td><input type="password" name="password" size=32 maxlength=32 value=""></td>
 </tr>
 <tr>
  <td>Permissions</td>
  <td><select name="perms">
        <option  value="guest">guest
        <option SELECTED value="editor">editor
        <option  value="admin">admin
      </select>
  </td>
 </tr>
 <tr>
  <td>Default Public</td>
  <td><input type="checkbox" name="default_public" {DEFAULT_PUBLIC_CHECKED}></td>
 </tr>
 <tr>
  <td>Include Public</td>
  <td><input type="checkbox" name="include_public" {INCLUDE_PUBLIC_CHECKED}></td>
 </tr>
 <tr>
  <td colspan=2 align=center>
   <input type="submit" name="bk_cancel_create" value="Cancel">
   <input type="submit" name="bk_user_create" value="Create User">
  </td>
 </tr>
</table>
</form>
