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

$users = getAllUsers();


printHTMLHead( getMLText("user_management") );
?>
<script language="JavaScript">

function checkForm(num)
{
	msg = "";
	eval("var formObj = document.form" + num + ";");
	
	if (formObj.login.value == "") msg += "<?printMLText("js_no_login");?>\n";
	if ((num == '0') && (formObj.pwd.value == "")) msg += "<?printMLText("js_no_pwd");?>\n";
	if (formObj.pwd.value != formObj.pwdconf.value) msg += "<?printMLText("js_pwd_not_conf");?>\n";
	if (formObj.name.value == "") msg += "<?printMLText("js_no_name");?>\n";
	if (formObj.email.value == "") msg += "<?printMLText("js_no_email");?>\n";
	if (formObj.comment.value == "") msg += "<?printMLText("js_no_comment");?>\n";
	if (msg != "")
	{
		alert(msg);
		return false;
	}
	else
		return true;
}


obj = -1;
function showUser(selectObj) {
	if (obj != -1)
		obj.style.display = "none";
	
	id = selectObj.options[selectObj.selectedIndex].value;
	if (id == -1)
		return;
	
	obj = document.getElementById("keywords" + id);
	obj.style.display = "";
}

</script>
<?
printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();

printStartBox(getMLText("add_user"));
?>
	<form action="../op/op.UsrMgr.php" method="post" enctype="multipart/form-data" name="form0" onsubmit="return checkForm('0');">
	<input type="Hidden" name="action" value="adduser">
	<table>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("user_login");?>:</td>
			<td><input name="login"></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("password");?>:</td>
			<td><input name="pwd" type="Password"></td>
		</tr>
		<tr>
			<td class="inputDescription"><?printMLText("confirm_pwd");?>:</td>
			<td><input type="Password" name="pwdconf"></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("user_name");?>:</td>
			<td><input name="name"></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("email");?>:</td>
			<td><input name="email"></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("comment");?>:</td>
			<td><textarea name="comment" rows="4" cols="30"></textarea></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("user_image");?>:</td>
			<td><input type="File" name="userfile"></td>
		</tr>
		<tr>
			<td colspan="2"><br><input type="Submit"></td>
		</tr>
	</table>
	</form>

<?
printNextBox(getMLText("edit_user"));
?>
	<table>
	<tr>
		<td class="inputDescription"><?=getMLText("user_name")?>:</td>
		<td>
			<select onchange="showUser(this)">
				<option value="-1"><?=getMLText("choose_user")?>
				<?
				foreach ($users as $currUser) {
					if (($currUser->getID() == $settings->_adminID) || ($currUser->getID() == $settings->_guestID))
						continue;
					
					print "<option value=\"".$currUser->getID()."\">" . $currUser->getFullName();
				}
				?>
			</select>
		</td>
	</tr>
	<?
	foreach ($users as $currUser) {
		if (($currUser->getID() == $settings->_adminID) || ($currUser->getID() == $settings->_guestID))
			continue;
	?>
	<tr id="keywords<?=$currUser->getID()?>" style="display : none;">
	<td colspan="2">
	
	<form action="../op/op.UsrMgr.php" method="post" enctype="multipart/form-data" name="form<?print $currUser->getID();?>" onsubmit="return checkForm('<?print $currUser->getID();?>');">
	<input type="Hidden" name="userid" value="<?print $currUser->getID();?>">
	<input type="Hidden" name="action" value="edituser">
	<table border="0">
		<tr>
			<td colspan="2"><hr size="1" width="100%" color="#000080" noshade></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("user_login");?>:</td>
			<td class="standardText"><input name="login" value="<?print $currUser->getLogin();?>"></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("password");?>:</td>
			<td><input type="Password" name="pwd"></td>
		</tr>
		<tr>
			<td class="inputDescription"><?printMLText("confirm_pwd");?>:</td>
			<td><input type="Password" name="pwdconf"></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("user_name");?>:</td>
			<td class="standardText"><input name="name" value="<?print $currUser->getFullName();?>"></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("email");?>:</td>
			<td class="standardText"><input name="email" value="<?print $currUser->getEmail();?>"></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("comment");?>:</td>
			<td class="standardText"><textarea name="comment" rows="4" cols="30"><?print $currUser->getComment();?></textarea></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("user_image");?>:</td>
			<td class="standardText">
				<?
					if ($currUser->hasImage())
						print "<img src=\"".$currUser->getImageURL()."\">";
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
	<hr size="1" width="80%" color="#000080" noshade>
	<a class="standardText" href="../op/op.UsrMgr.php?userid=<?print $currUser->getID();?>&action=removeuser"><img src="images/del.gif" width="15" height="15" border="0" align="absmiddle" alt=""> <?printMLText("rm_user");?></a>
	
	</td>
	</tr>
<?  } ?>
</table>

<?
printEndBox();

printCenterEnd();
printHTMLFoot();
?>