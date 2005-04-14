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



printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
printTitleBar($folder);
printCenterStart();

printStartBox(getMLText("rm_folder"));

print "<div class=\"standardText\">";


if ($folder->getAccessMode($user) < M_ALL)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	printMLText("removing_folder");
	
	$parent = $folder->getParent();
	
	if (!$folder->remove())
	{
		printMLText("error_occured");
		printGoBack();
	}
	else
	{
		printMLText("op_finished");
		printGoto(array(array($parent->getName(), "../out/out.ViewFolder.php?folderid=".$parent->getID())));
	}
}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>