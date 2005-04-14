<?
include("../inc/inc.Settings.php");
include("../inc/inc.AccessUtils.php");
include("../inc/inc.ClassAccess.php");
include("../inc/inc.ClassDocument.php");
include("../inc/inc.ClassFolder.php");
include("../inc/inc.ClassGroup.php");
include("../inc/inc.ClassUser.php");
include("../inc/inc.DBAccess.php");
include("../inc/inc.FileUtils.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");
include("../inc/inc.Authentication.php");



printHTMLHead( getMLText("my_account") );
?>

<script language="JavaScript">

function checkForm()
{
	msg = "";
	if (document.form1.pwd.value != document.form1.pwdconf.value) msg += "<?printMLText("js_pwd_not_conf");?>\n";
	if (document.form1.fullname.value == "") msg += "<?printMLText("js_no_name");?>\n";
	if (document.form1.email.value == "") msg += "<?printMLText("js_no_email");?>\n";
	if (document.form1.comment.value == "") msg += "<?printMLText("js_no_comment");?>\n";
	if (msg != "")
	{
		alert(msg);
		return false;
	}
	else
		return true;
}
</script>

<?
printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();
printStartBox(getMLText("my_account"));
?>

<form action="../op/op.EditUserData.php" enctype="multipart/form-data" method="post" name="form1" onsubmit="return checkForm();">
<table>
	<tr>
		<td class="inputDescription"><?printMLText("password");?>:</td>
		<td><input type="Password" name="pwd"></td>
	</tr>
	<tr>
		<td class="inputDescription"><?printMLText("confirm_pwd");?>:</td>
		<td><input type="Password" name="pwdconf"></td>
	</tr>
	<tr>
		<td class="inputDescription"><?printMLText("name");?>:</td>
		<td><input name="fullname" value="<?print $user->getFullName();?>"></td>
	</tr>
	<tr>
		<td class="inputDescription"><?printMLText("email");?>:</td>
		<td><input name="email" value="<?print $user->getEmail();?>"></td>
	</tr>
	<tr>
		<td class="inputDescription" valign="top"><?printMLText("comment");?>:</td>
		<td><textarea name="comment" rows="4" cols="30"><?print $user->getComment();?></textarea></td>
	</tr>
	<tr>
		<td class="inputDescription" valign="top"><?printMLText("user_image");?>:</td>
		<td class="standardText">
			<?
				if ($user->hasImage())
					print "<img src=\"".$user->getImageURL()."\">";
				else
					printMLText("no_user_image");
			?>
			
		</td>
	</tr>
	<tr>
		<td class="inputDescription" valign="top"><?printMLText("new_user_image");?>:</td>
		<td class="standardText"><input type="file" name="userfile" accept="image/jpeg"></td>
	</tr>
	<tr>
		<td colspan="2"><br><input type="Submit"></td>
	</tr>
</table>
</form>

<?
if (!$user->isAdmin()) {
	printNextBox(getMLText("personal_default_keywords"));
	print "<div class=\"standardText\">";
	print "<a href=\"out.DefaultKeywords.php\">" . getMLText("edit_personal_default_keywords") . "</a>";
	print "</div>";
}

printEndBox();
printCenterEnd();
printHTMLFoot();
?>