<?
include("../inc/inc.Settings.php");
include("../inc/inc.AccessUtils.php");
include("../inc/inc.ClassAccess.php");
include("../inc/inc.ClassFolder.php");
include("../inc/inc.ClassDocument.php");
include("../inc/inc.ClassGroup.php");
include("../inc/inc.ClassUser.php");
include("../inc/inc.DBAccess.php");
include("../inc/inc.FileUtils.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");

include("../inc/inc.Authentication.php");

$document = getDocument($documentid);
$notifyList = $document->getNotifyList();

if ($document->getAccessMode($user) < M_READWRITE)
	die("access denied");


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
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
printTitleBar($document->getFolder());
printDocumentPageStart($document);
printPageHeader(getMLText("edit_document_notify") . ": " . $document->getName());

printStartBox(getMLText("edit_existing_notify"));
?>

<table border="0" cellpadding="5" cellspacing="0">
	<?
		if ((count($notifyList["users"]) == 0) && (count($notifyList["groups"]) == 0))
		{
			print "<tr><td class=\"notifylist\">".getMLText("empty_notify_list")."</td></tr>";
		}
		else
		{
			print "<tr>\n";
			print "	<td style=\"border-bottom: 1pt solid #000080;\">&nbsp;</td>\n";
			print "	<td style=\"border-bottom: 1pt solid #000080;\" class=\"notifylist\"><i>".getMLText("name")."</i></td>\n";
			print "	<td style=\"border-bottom: 1pt solid #000080;\">&nbsp;</td>\n";
			print "</tr>\n";
			foreach ($notifyList["users"] as $userNotify)
			{
				print "<tr>";
				print "<td><img src=\"images/usericon.gif\" width=16 height=16></td>";
				print "<td class=\"notifylist\">" . $userNotify->getFullName() . "</td>";
				print "<td><a href=\"../op/op.DocumentNotify.php?documentid=". $documentid . "&action=delnotify&userid=".$userNotify->getID()."\"><img src=\"images/del.gif\" width=15 height=15 border=0></a>";
				print "</tr>";
			}
			
			foreach ($notifyList["groups"] as $groupNotify)
			{
				print "<tr>";
				print "<td><img src=\"images/groupicon.gif\" width=16 height=16 border=0></td>";
				print "<td class=\"notifylist\">" . $groupNotify->getName() . "</td>";
				print "<td><a href=\"../op/op.DocumentNotify.php?documentid=". $documentid . "&action=delnotify&groupid=".$groupNotify->getID()."\"><img src=\"images/del.gif\" width=15 height=15 border=0></a>";
				print "</tr>";
			}
		}
	?>
</table>

<?
printNextBox(getMLText("add_new_notify"));
?>


<form action="../op/op.DocumentNotify.php" name="form1" onsubmit="return checkForm();">
<input type="Hidden" name="documentid" value="<?print $documentid?>">
<input type="Hidden" name="action" value="addnotify">
<table>
	<tr>
		<td class="inputDescription"><?printMLText("user");?>:</td>
		<td>
			<select name="userid">
				<option value="-1"><?printMLText("select_one");?>
				<option value="-1">-------------------------------
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
		<td colspan="2"><br><input type="Submit"></td>
	</tr>
</table>
</form>

<?
printEndBox();
printDocumentPageEnd($document);
printHTMLFoot();
?>
