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


$documentid = (int)$_GET['documentid'];
$targetid = (int)$_GET['targetid'];

$document = getDocument($documentid);
$oldFolder = $document->getFolder();
$targetFolder = getFolder($targetid);


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("move_document"));

print "<div class=\"standardText\">";


if (($document->getAccessMode($user) < M_READWRITE) || ($targetFolder->getAccessMode($user) < M_READWRITE))
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	printMLText("moving_document");
	
	if ($document->setFolder($targetFolder))
	{
		printMLText("op_finished");
		printGoto(array(array($document->getName(), "../out/out.ViewDocument.php?documentid=".$document->getID()), 
				array($oldFolder->getName(), "../out/out.ViewFolder.php?folderid=".$oldFolder->getID())));
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
