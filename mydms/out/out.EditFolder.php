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

$parent = ($folder->getID() == $settings->_rootFolderID) ? false : $folder->getParent();

if ($folder->getAccessMode($user) < M_READWRITE)
	die ("Access denied");


printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
?>

<script language="JavaScript">
function checkForm()
{
	msg = "";
	if (document.form1.fname.value == "") msg += "<?printMLText("js_no_name");?>\n";
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
printPageHeader(getMLText("edit_folder_props") . ": " . $folder->getName());

printStartBox(getMLText("edit_folder_props"));
?>

<form action="../op/op.EditFolder.php" name="form1" onsubmit="return checkForm();">
	<input type="Hidden" name="folderid" value="<?print $folderid;?>">
	<table>
		<tr>
			<td class="inputDescription"><?printMLText("name");?>:</td>
			<td><input name="fname" value="<?print $folder->getName();?>"></td>
		</tr>
		<tr>
			<td valign="top" class="inputDescription"><?printMLText("comment");?>:</td>
			<td><textarea name="comment" rows="4" cols="30"><?print $folder->getComment();?></textarea></td>
		</tr>
		<?
			if ($parent && $parent->getAccessMode($user) > M_READ)
			{
				print "<tr>";
				print "<td class=\"inputDescription\">" . getMLText("sequence") . ":</td>";
				print "<td>";
				printSequenceChooser($parent->getSubFolders(), $folder->getID());
				print "</td></tr>";
			}
		?>
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