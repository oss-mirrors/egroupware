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
	die ("Access denied");


printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
printTitleBar($folder);
printFolderPageStart($folder);
printPageHeader(getMLText("move_folder") . ": " . $folder->getName());

printStartBox(getMLText("move_folder"));
?>

<form action="../op/op.MoveFolder.php" name="form1">
	<input type="Hidden" name="folderid" value="<?print $folderid;?>">
	<table>
		<tr>
			<td class="inputDescription"><?printMLText("choose_target_folder");?>:</td>
			<td><?printFolderChooser("form1", M_READWRITE, $folder->getID());?></td>
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