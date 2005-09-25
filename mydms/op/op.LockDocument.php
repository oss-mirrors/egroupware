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

$documentid = (int)$_GET['documentid'];

$document = getDocument($documentid);


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("lock_document"));

print "<div class=\"standardText\">";


if ($document->getAccessMode($user) < M_READWRITE)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	if ($document->isLocked())
	{
		printMLText("document_already_locked");
		printGoBack();
	}
	else
	{
		printMLText("locking_document");
		if ($document->setLocked($user))
		{
			printMLText("op_finished");
			printGoto(array(array($document->getName(), "../out/out.ViewDocument.php?documentid=".$document->getID())));
		}
		else
		{
			printMLText("error_occured");
			printGoBack();
		}
	}
}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>