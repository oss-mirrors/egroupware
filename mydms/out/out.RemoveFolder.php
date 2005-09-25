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

$folderid = $_GET['folderid'];
$folder = getFolder($folderid);

if ($folder->getAccessMode($user) < M_ALL)
	die ("Access denied");


printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );

printTitleBar($folder);
printFolderPageStart($folder);
printPageHeader(getMLText("rm_folder") . ": " . $folder->getName());

printStartBox(getMLText("rm_folder"));
?>

<form action="../op/op.RemoveFolder.php" name="form1">
	<input type="Hidden" name="folderid" value="<?print $folderid;?>">
	<div class="standardText">
	<? printMLText("confirm_rm_folder", array ("foldername" => $folder->getName()));?>
	</div><br>
	<input type="Submit" value="<?printMLText("rm_folder");?>">
</form>


<?

printEndBox();
printFolderPageEnd($folder);
printHTMLFoot();

?>