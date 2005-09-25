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


function printAccessModeSelection($defMode)
{
	print "<select name=\"mode\">\n";
	print "\t<option value=\"".M_NONE."\"" . (($defMode == M_NONE) ? " selected" : "") . ">" . getMLText("access_mode_none") . "\n";
	print "\t<option value=\"".M_READ."\"" . (($defMode == M_READ) ? " selected" : "") . ">" . getMLText("access_mode_read") . "\n";
	print "\t<option value=\"".M_READWRITE."\"" . (($defMode == M_READWRITE) ? " selected" : "") . ">" . getMLText("access_mode_readwrite") . "\n";
	print "\t<option value=\"".M_ALL."\"" . (($defMode == M_ALL) ? " selected" : "") . ">" . getMLText("access_mode_all") . "\n";
	print "</select>\n";
}

$folderid	= (int)$_GET['folderid'];
$folder		= getFolder($folderid);
$allUsers = getAllUsers();

if ($folder->getAccessMode($user) < M_ALL)
	die ("Access denied");


printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
?>

<script language="JavaScript">
function checkForm()
{
	msg = "";
	if ((document.form1.userid.options[document.form1.userid.selectedIndex].value == -1) && 
		(document.form1.groupid.options[document.form1.groupid.selectedIndex].value == -1))
			msg += "<?printMLText("js_select_user_or_group");?>\n";
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
printTitleBar($folder);
printFolderPageStart($folder);
printPageHeader(getMLText("edit_folder_access") . ": " . $folder->getName());


//Nur admin darf Besitzer ändern
if ($user->isAdmin())
{
	printStartBox(getMLText("set_owner"));
	?>
		<form action="../op/op.FolderAccess.php">
		<input type="Hidden" name="action" value="setowner">
		<input type="Hidden" name="folderid" value="<?print $folderid;?>">
		<table>
			<tr>
				<td class="inputDescription"><?printMLText("owner");?></td>
				<td>
					<select name="ownerid">
						<?
							$owner = $folder->getOwner();
							foreach ($allUsers as $currUser)
							{
								if ($currUser->getID() == $settings->_guestID)
									continue;
								print "<option value=\"".$currUser->getID()."\"";
								if ($currUser->getID() == $owner->getID())
									print " selected";
								print ">" . $currUser->getFullname() . "\n";
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2"><br><input type="Submit"></td>
			</tr>
		</table>
		</form>
	<?
	printNextBox(getMLText("edit_inherit_access"));
}
else
	printStartBox(getMLText("edit_inherit_access"));

print "<div class=\"inheritAccess\">";

if ($folder->inheritsAccess())
{
	printMLText("inherits_access_msg", array(
		"copyurl" => "../op/op.FolderAccess.php?folderid=".$folderid."&action=notinherit&mode=copy", 
		"emptyurl" => "../op/op.FolderAccess.php?folderid=".$folderid."&action=notinherit&mode=empty"));
	printEndBox();
	printFolderPageEnd($folder);
	printHTMLFoot();
	exit();
}

printMLText("does_not_inherit_access_msg", array("inheriturl" => "../op/op.FolderAccess.php?folderid=".$folderid."&action=inherit"));
print "</div>";


$accessList = $folder->getAccessList();

printNextBox(getMLText("default_access"));
?>

<div class="defaultAccess">
<form action="../op/op.FolderAccess.php">
	<input type="Hidden" name="folderid" value="<?print $folderid;?>">
	<input type="Hidden" name="action" value="setdefault">
	
	<? printAccessModeSelection($folder->getDefaultAccess()); ?>
	<p>
	<input type="Submit">
</form>
</div>

<?
printNextBox(getMLText("edit_existing_access"));
?>

<table border="0" cellpadding="0" cellspacing="5">
	<?
		if ((count($accessList["users"]) == 0) && (count($accessList["groups"]) == 0))
		{
			print "<tr><td class=\"accessList\">".getMLText("empty_access_list")."</td></tr>";
		}
		else
		{
			$rownum = count($accessList["users"])+count($accessList["groups"])+1;
			?>
				<tr>
					<td></td>
					<td class="accessList" style="border-bottom: 1pt solid #000080;"><i><?printMLText("name");?></i></td>
					<td rowspan="<?print $rownum;?>" style="border-left: 1pt solid #000080;">&nbsp;</td>
					<td class="accessList" style="border-bottom: 1pt solid #000080;"><i><?printMLText("access_mode");?></i></td>
					<td></td>
				</tr>
				<?
					foreach ($accessList["users"] as $userAccess)
					{
						$userObj = $userAccess->getUser();
						print "<form action=\"../op/op.FolderAccess.php\">\n";
						print "<input type=\"Hidden\" name=\"folderid\" value=\"".$folderid."\">\n";
						print "<input type=\"Hidden\" name=\"action\" value=\"editaccess\">\n";
						print "<input type=\"Hidden\" name=\"userid\" value=\"".$userObj->getID()."\">\n";
						print "<tr>\n";
						print "<td><img src=\"images/usericon.gif\" width=16 height=16></td>\n";
						print "<td class=\"accessList\">". $userObj->getFullName() . "</td>\n";
						print "<td>\n";
						printAccessModeSelection($userAccess->getMode());
						print "</td>\n";
						print "<td><input type=\"Image\" src=\"images/save.gif\"></td>\n";
						print "<td><a href=\"../op/op.FolderAccess.php?folderid=".$folderid."&action=delaccess&userid=".$userObj->getID()."\"><img src=\"images/del.gif\" width=15 height=15 border=0></a></td>\n";
						print "</tr>\n";
						print "</form>\n";
					}
					
					foreach ($accessList["groups"] as $groupAccess)
					{
						$groupObj = $groupAccess->getGroup();
						$mode = $groupAccess->getMode();
						print "<form action=\"../op/op.FolderAccess.php\">";
						print "<input type=\"Hidden\" name=\"folderid\" value=\"".$folderid."\">";
						print "<input type=\"Hidden\" name=\"action\" value=\"editaccess\">";
						print "<input type=\"Hidden\" name=\"groupid\" value=\"".$groupObj->getID()."\">";
						print "<tr>";
						print "<td><img src=\"images/groupicon.gif\" width=16 height=16 border=0></td>";
						print "<td class=\"accessList\">". $groupObj->getName() . "</td>";
						print "<td>";
						printAccessModeSelection($groupAccess->getMode());						print "</td>\n";
						print "<td><input type=\"Image\" src=\"images/save.gif\"></td>";
						print "<td><a href=\"../op/op.FolderAccess.php?folderid=".$folderid."&action=delaccess&groupid=".$groupObj->getID()."\"><img src=\"images/del.gif\" width=15 height=15 border=0></a></td>";
						print "</tr>";
						print "</form>";
					}
		}
	?>
</table>

<?
printNextBox(getMLText("add_access"));
?>

<form action="../op/op.FolderAccess.php" name="form1" onsubmit="return checkForm();">
	<input type="Hidden" name="folderid" value="<?print $folderid?>">
	<input type="Hidden" name="action" value="addaccess">
	<table>
	<tr>
		<td class="inputDescription"><?printMLText("user");?>:</td>
		<td>
			<select name="userid">
				<option value="-1"><?printMLText("select_one");?>
				<option value="-1">-------------------------------
				<?
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
		<td class="inputDescription"><?printMLText("group");?>:</td>
		<td>
			<select name="groupid">
				<option value="-1"><?printMLText("select_one");?>
				<option value="-1">-------------------------------
				<?
					$allGroups = getAllGroups();
					foreach ($allGroups as $groupObj)
						print "<option value=\"".$groupObj->getID()."\">" . $groupObj->getName() . "\n";
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="inputDescription"><?printMLText("access_mode");?>:</td>
		<td>
			<?
				printAccessModeSelection(M_READ);
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
printFolderPageEnd($folder);
printHTMLFoot();
?>