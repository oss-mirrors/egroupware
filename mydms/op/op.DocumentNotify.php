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

$document = getDocument($documentid);


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("edit_document_notify"));

print "<div class=\"standardText\">";


if ($document->getAccessMode($user) < M_READWRITE)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	//Benachrichtigung löschen ------------------------------------------------------------------------
	if ($action == "delnotify")
	{
		printMLText("deleting_document_notify");
		if (isset($userid)) {
			if (!is_numeric($userid))
				die ("invalid user id");
			$document->removeNotify($userid, true);
		}
		else if (isset($groupid)) {
			if (!is_numeric($groupid))
				die ("invalid group id");
			$document->removeNotify($groupid, false);
		}
	}
	
	//Benachrichtigung hinzufügen ---------------------------------------------------------------------
	else if ($action == "addnotify")
	{
		printMLText("adding_document_notify");
		if ($userid != -1) {
			if (!is_numeric($userid))
				die ("invalid user id");
			$document->addNotify($userid, true);
		}
		if ($groupid != -1) {
			if (!is_numeric($groupid))
				die ("invalid group id");
			$document->addNotify($groupid, false);
		}
	}
	printMLText("op_finished");
	printGoto(array(array($document->getName(), "../out/out.ViewDocument.php?documentid=".$document->getID()), 
				array(getMLText("document_notify_again"), "../out/out.DocumentNotify.php?documentid=".$document->getID())));
}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>