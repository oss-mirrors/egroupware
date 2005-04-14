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


$folder = getFolder($folderid);
$oldParent = $folder->getParent();
$targetFolder = getFolder($targetid);


printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
printTitleBar($folder);
printCenterStart();

printStartBox(getMLText("move_folder"));

print "<div class=\"standardText\">";


if (($folder->getAccessMode($user) < M_READWRITE) || ($targetFolder->getAccessMode($user) < M_READWRITE))
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	printMLText("moving_folder");
	
	if ($folder->setParent($targetFolder))
	{
		printMLText("op_finished");
		printGoto(array(array($folder->getName(), "../out/out.ViewFolder.php?folderid=".$folder->getID()), 
				array($oldParent->getName(), "../out/out.ViewFolder.php?folderid=".$oldParent->getID())));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();


?>
