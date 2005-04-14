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

printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("unlock_document"));

print "<div class=\"standardText\">";


if ($document->getAccessMode($user) < M_READWRITE)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	if (!$document->isLocked())
	{
		printMLText("document_is_not_locked");
		printGoBack();
	}
	else
	{
		$lockingUser = $document->getLockingUser();
		if (($lockingUser->getID() == $user->getID()) || ($document->getAccessMode($user) == M_ALL))
		{
			printMLText("unlocking_document");
			if ($document->setLocked("false"))
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
		else
		{
			printMLText("unlocking_denied");
			printGoBack();
		}
	}
}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>
