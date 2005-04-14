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
include("../inc/inc.Utils.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");
include("../inc/inc.Authentication.php");

$document = getDocument($documentid);

if ($document->getAccessMode($user) < M_READWRITE)
	die ("Access denied");


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
?>

<script language="JavaScript">
function checkForm()
{
	msg = "";
	if (document.form1.userfile.value == "") msg += "<?printMLText("js_no_file");?>\n";
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
printTitleBar($document->getFolder());
printDocumentPageStart($document);
printPageHeader(getMLText("update_document") . ": " . $document->getName());

printStartBox(getMLText("update_document"));

if ($document->isLocked())
{
	print "<div class=\"msgLocked\">";
	$lockingUser = $document->getLockingUser();
	
	printMLText("update_locked_msg", array("username" => $lockingUser->getFullName(), "email" => $lockingUser->getEmail()));
	
	if ($lockingUser->getID() == $user->getID())
		printMLText("unlock_cause_locking_user");
	else if ($document->getAccessMode($user) == M_ALL)
		printMLText("unlock_cause_access_mode_all");
	else
	{
		printMLText("no_update_cause_locked");
		print "</div>";
		printEndBox();
		printFolderPageEnd($folder);
		printHTMLFoot();
		exit;
	}
	print "</div>";
}

?>

<form action="../op/op.UpdateDocument.php" enctype="multipart/form-data" method="post" name="form1" onsubmit="return checkForm();">
	<input type="Hidden" name="documentid" value="<? print $documentid; ?>">
	<table>
		<tr>
			<td class="inputDescription"><?printMLText("local_file");?>:</td>
			<td><input type="File" name="userfile"></td>
		</tr>
		<tr>
			<td valign="top" class="inputDescription"><?printMLText("comment");?>:</td>
			<td class="standardText">
				<textarea name="comment" rows="4" cols="30"></textarea>
			</td>
		</tr>
		<tr>
			<td valign="top" class="inputDescription"><?printMLText("expires");?>:</td>
			<td class="standardText">
				<input type="Radio" name="expires" value="false"<?if (!$document->expires()) print " checked";?>><?printMLText("does_not_expire");?><br>
				<input type="radio" name="expires" value="true"<?if ($document->expires()) print " checked";?>><?printDateChooser(-1, "exp");?>
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