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

$folderid = (isset($_GET['folderid'])) ? $_GET['folderid'] : 1;
$folder = getFolder($folderid);

if ($folder->getAccessMode($user) < M_READWRITE)
	die("access denied");


printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
?>

<script language="JavaScript">
function checkForm()
{
	msg = "";
	if (document.form1.name.value == "") msg += "<?printMLText("js_no_name");?>\n";
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
printTitleBar($folder);
printFolderPageStart($folder);
printPageHeader(getMLText("add_subfolder") . ": " . $folder->getName());

printStartBox(getMLText("add_subfolder"));
?>

<form action="../op/op.AddSubFolder.php" name="form1" onsubmit="return checkForm();">
	<input type="Hidden" name="folderid" value="<?print $folderid;?>">
	<table border="0" width="100%">
		<tr>
			<td class="inputDescription" width="150px"><?printMLText("name");?>:</td>
			<td><input name="fname" style="width: 100%;"></td>
		</tr>
		<tr>
			<td valign="top" class="inputDescription"><?printMLText("comment");?>:</td>
			<td><textarea name="comment" rows="4" style="width: 100%;"></textarea></td>
		</tr>
		<tr>
			<td class="inputDescription"><?printMLText("sequence");?>:</td>
			<td><?printSequenceChooser($folder->getSubFolders());?></td>
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