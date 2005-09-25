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

$folderid = (int)$_GET['folderid'];
$folder = getFolder($folderid);

$action		= $_GET['action'];
$userid		= $_GET['userid'];
$groupid	= $_GET['groupid'];


printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
printTitleBar($folder);
printCenterStart();

printStartBox(getMLText("edit_folder_notify"));

print "<div class=\"standardText\">";


if ($folder->getAccessMode($user) < M_READWRITE)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	//Benachrichtigung löschen ------------------------------------------------------------------------
	if ($action == "delnotify")
	{
		printMLText("deleting_folder_notify");
		if (isset($userid)) {
			if (!is_numeric($userid))
				die ("invalid user id");
			$folder->removeNotify($userid, true);
		}
		else if (isset($groupid)) {
			if (!is_numeric($groupid))
				die ("invalid group id");
			$folder->removeNotify($groupid, false);
		}
	}

	//Benachrichtigung hinzufügen ---------------------------------------------------------------------
	else if ($action == "addnotify")
	{
		printMLText("adding_folder_notify");
		if ($userid != -1) {
			if (!is_numeric($userid))
				die ("invalid user id");
			$folder->addNotify($userid, true);
		}
		if ($groupid != -1) {
			if (!is_numeric($groupid))
				die ("invalid group id");
			$folder->addNotify($groupid, false);
		}
	}
	
	printMLText("op_finished");
	printGoto(array(array($folder->getName(), "../out/out.ViewFolder.php?folderid=".$folder->getID()), 
				array(getMLText("folder_notify_again"), "../out/out.FolderNotify.php?folderid=".$folder->getID())));
}



print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>