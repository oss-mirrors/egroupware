<?
include("../inc/inc.Settings.php");
include("../inc/inc.AccessUtils.php");
include("../inc/inc.ClassAccess.php");
include("../inc/inc.ClassDocument.php");
include("../inc/inc.ClassFolder.php");
include("../inc/inc.ClassGroup.php");
include("../inc/inc.ClassUser.php");
include("../inc/inc.DBAccess.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");
include("../inc/inc.Authentication.php");

$currentFolder = getFolder($folderid);

printHTMLHead( getMLText("search") );
?>

<script language="JavaScript">

function checkForm()
{
	msg = "";
	if (document.form1.query.value == "")
	{
		if (document.form1.creationdate.checked || document.form1.lastupdate.checked)
			document.form1.query.value = "%"
		else
			msg += "<?printMLText("js_no_query");?>\n";
	}
	
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
printTitleBar($currentFolder);
printCenterStart();

printStartBox(getMLText("search"));
?>

<form action="../op/op.Search.php" name="form1" onsubmit="return checkForm();">

<table cellpadding="3">
	<tr>
		<td class="inputDescription"><?printMLText("search_query");?>:</td>
		<td>
			<input name="query">
			<select name="mode">
				<option value="and" selected><?printMLText("search_mode_and");?><br>
				<option value="or"><?printMLText("search_mode_or");?>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top" class="inputDescription"><?printMLText("search_in");?>:</td>
		<td class="standardText">
			<input type="Checkbox" name="searchin[]" value="keywords" checked><?printMLText("keywords");?><br>
			<input type="Checkbox" name="searchin[]" value="name"><?printMLText("name");?><br>
			<input type="Checkbox" name="searchin[]" value="comment"><?printMLText("comment");?>
		</td>
	</tr>
	<tr>
		<td valign="top" class="inputDescription"><?printMLText("owner");?>:</td>
		<td class="standardText">
			<select name="ownerid">
			<option value="-1"><?printMLText("all_users");?>
			<?
				$allUsers = getAllUsers();
				foreach ($allUsers as $userObj)
				{
					if ($userObj->getID() == $settings->_guestID)
						continue;
					print "<option value=\"".$userObj->getID()."\">" . $userObj->getFullName() . "\n";
				}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top" class="inputDescription"><?printMLText("under_folder")?>:</td>
		<td class="standardText"><?printFolderChooser("form1", M_READ, -1, $currentFolder);?></td>
	</tr>
	<tr>
		<td valign="top" class="inputDescription"><?printMLText("creation_date");?>:</td>
		<td class="standardText">
			<input type="Checkbox" name="creationdate" value="true">
			<?
				printMLText("between");
				print "&nbsp;&nbsp;";
				printDateChooser(-1, "createstart");
				print "&nbsp;&nbsp;";
				printMLText("and");
				print "&nbsp;&nbsp;";
				printDateChooser(-1, "createend");
			?>
		</td>
	</tr>
	<tr>
		<td valign="top" class="inputDescription"><?printMLText("last_update");?>:</td>
		<td class="standardText">
			<input type="Checkbox" name="lastupdate" value="true">
			<?
				printMLText("between");
				print "&nbsp;&nbsp;";
				printDateChooser(-1, "updatestart");
				print "&nbsp;&nbsp;";
				printMLText("and");
				print "&nbsp;&nbsp;";
				printDateChooser(-1, "updateend");
			?>
		</td>
	</tr>
	<tr>
		<td colspan="2"><br><input type="Submit"></td>
	</tr>

</table>

</form>

<?
printEndBox();
printCenterEnd();
printHTMLFoot();
?>