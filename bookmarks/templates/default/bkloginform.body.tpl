<!-- $Id$ -->
<script type="text/javascript" src="md5.js"></script>
<script type="text/javascript">
<!-- start Javascript
  function doChallengeResponse() {
    str = document.login.username.value + ":" +
          MD5(document.login.password.value) + ":" +
          document.login.challenge.value;

    document.login.response.value = MD5(str);
    document.login.password.value = "";
    document.login.submit();
  }
// end Javascript -->
</script>

<p>&nbsp;
<p>You have attempted an action that requires you to be authenticated.<br>
Please enter your bookmarker user ID and password.

<form name="login" action="{FORM_ACTION}" method=post>
<!-- Set up the form with the challenge value and an empty reply value -->
<input type="hidden" name="challenge" value="{CHALLENGE}">
<input type="hidden" name="response"  value="">

<table border=0 bgcolor="#EEEEEE" align="center" cellspacing=0 cellpadding=4>
 <tr valign=center align=left>
  <td>Username:</td>
  <td align=left><input type="text" name="username" value="{DEFAULT_USERNAME}" size=15 maxlength=32></td>
 </tr>
 <tr valign=center align=left>
  <td>Password:</td>
  <td align=left><input type="password" name="password" size=15 maxlength=32></td>
 </tr>
 <tr valign=center align=left>
  <td>Save my login information<br>on my computer
  </td>
  <td align=left><input type=checkbox name=save_auth>
  </td>
 <tr>
  <td colspan=2 align=right>
    <strong><a href="{CANCEL_LOGIN}">Cancel</a></strong>
    &nbsp;&nbsp;
    <input onClick="doChallengeResponse(); return false;" type="submit" name="submitbtn" value="Login">
  </td>
 </tr>
</table>
</form>
{INVALID_MSG}
<script language="JavaScript">
<!--
  // Activate the appropriate input form field.
  if (document.login.username.value == "") {
    document.login.username.focus();
  } else {
    document.login.password.focus();
  }
// -->
</script>
